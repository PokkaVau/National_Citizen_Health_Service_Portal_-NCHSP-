<?php
require('../config/db.php');
require('../auth_session.php');
require('../config/secrets.php'); // Contains GROQ_API_KEY

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input_text = isset($_POST['symptoms']) ? trim($_POST['symptoms']) : '';
$include_history = isset($_POST['include_history']) && $_POST['include_history'] === 'true';

// 1. Fetch User Context (if requested)
$context_str = "";
if ($include_history) {
    try {
        // Fetch Medications
        $stmt = $pdo->prepare("SELECT name, dosage, frequency FROM medications WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $meds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Recent Reports
        $stmt = $pdo->prepare("SELECT test_name, test_date, result_value, reference_range FROM reports WHERE user_id = ? ORDER BY test_date DESC LIMIT 5");
        $stmt->execute([$user_id]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Basic Vitals
        $stmt = $pdo->prepare("SELECT dob, weight, height, blood_type FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $age = date_diff(date_create($user['dob']), date_create('today'))->y;

        $context_str .= "## Patient Context\n";
        $context_str .= "- Age: $age, Sex: Unknown (assume N/A), Weight: {$user['weight']}kg\n";
        $context_str .= "- Current Medications: " . json_encode($meds) . "\n";
        $context_str .= "- Recent Lab Results: " . json_encode($reports) . "\n";
    } catch (Exception $e) {
        // Continue without context if DB fails (shouldn't break the whole feature)
    }
}

// 2. Handle File Upload (Image)
$image_content = null;
if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['prescription']['tmp_name'];
    $file_type = $_FILES['prescription']['type'];

    // Check if image
    if (strpos($file_type, 'image/') === 0) {
        $data = file_get_contents($file_tmp);
        $base64 = base64_encode($data);
        $image_content = "data:$file_type;base64,$base64";
    }
}

// 3. Construct Payload for Groq
$messages = [
    [
        "role" => "system",
        "content" => "You are an AI Medical Assistant. Your goal is to summarize medical inputs (prescriptions, reports, or symptoms) into a structured, easy-to-read format for the patient. 
        
        Structure your response as follows (Markdown):
        ## Summary
        [Brief summary of the main points]
        
        ## Key Instructions
        - [List actionable items, e.g. medication timing, lifestyle changes]
        
        ## Medical Context Analysis
        [If patient history was provided, analyze potential interactions or relevant trends. If not, omit this section.]
        
        ## Disclaimer
        [Standard disclaimer that this is AI-generated and not a substitute for professional medical advice.]"
    ]
];

// User Message construction
$user_message_content = [];

// Add text context
$text_prompt = "Please analyze the following information.\n\n";
if (!empty($input_text)) {
    $text_prompt .= "Patient Notes/Symptoms:\n$input_text\n\n";
}
if (!empty($context_str)) {
    $text_prompt .= "$context_str\n\n";
}

$user_message_content[] = [
    "type" => "text",
    "text" => $text_prompt
];

// Add image if present
$model = "llama-3.3-70b-versatile"; // Default text model
if ($image_content) {
    $model = "meta-llama/llama-4-scout-17b-16e-instruct"; // Vision model

    // Explicit instruction for Vision model
    $text_prompt .= "\n\nIMPORTANT: Start by transcribing and analyzing the text visible in the attached image (prescription or medical report). Then combine it with the context below.";

    $user_message_content[] = [
        "type" => "image_url",
        "image_url" => [
            "url" => $image_content
        ]
    ];

    // Update the text part of the message with the new prompt
    $user_message_content[0]['text'] = $text_prompt;
}

$messages[] = [
    "role" => "user",
    "content" => $user_message_content
];

// 4. Call Groq API
$payload = [
    "model" => $model,
    "messages" => $messages,
    "temperature" => 0.6,
    "max_tokens" => 1024
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, GROQ_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . GROQ_API_KEY,
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(500);
    echo json_encode(['error' => "Connection Error: $curl_error"]);
    exit;
}

if ($http_code !== 200) {
    http_response_code(500);
    echo json_encode(['error' => "API Error ($http_code): " . $response]);
    exit;
}

// 5. Return Result
$result = json_decode($response, true);
$summary = $result['choices'][0]['message']['content'] ?? "Could not generate summary.";

echo json_encode(['success' => true, 'summary' => $summary]);
?>