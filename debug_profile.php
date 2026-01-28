<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('config/db.php');
require('auth_session.php');

// Simulate session if needed for testing CLI, but here we assume browser run
if (!isset($_SESSION['user_id'])) {
    echo "No user logged in.\n";
    // For debugging, we can try to fetch the first user
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch();
    if ($user) {
        echo "Found a user for testing: " . $user['id'] . "\n";
        $_SESSION['user_id'] = $user['id'];
        $user_id = $user['id'];
    } else {
        echo "No users in database.\n";
        exit;
    }
} else {
    $user_id = $_SESSION['user_id'];
    echo "User ID from session: " . $user_id . "\n";
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "User fetched successfully.\n";
    echo "Keys available: " . implode(", ", array_keys($user)) . "\n";

    // Check specific fields used in profile.php
    $fields = ['profile_picture', 'name', 'mobile', 'voter_id', 'dob', 'weight', 'height', 'blood_type'];
    foreach ($fields as $field) {
        if (array_key_exists($field, $user)) {
            echo "Field '$field' exists. Value: " . ($user[$field] ?? 'NULL') . "\n";
        } else {
            echo "ERROR: Field '$field' MISSING in result set!\n";
        }
    }
} else {
    echo "User not found in DB.\n";
}

// Check directory
$upload_dir = 'uploads/profiles/';
if (is_dir($upload_dir)) {
    echo "Upload directory exists.\n";
    echo "Writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "\n";
} else {
    echo "Upload directory does NOT exist.\n";
    if (mkdir($upload_dir, 0777, true)) {
        echo "Created upload directory successfully.\n";
    } else {
        echo "Failed to create upload directory.\n";
    }
}
?>