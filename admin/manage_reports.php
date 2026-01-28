<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";

// Handle Upload
if (isset($_POST['upload'])) {
    $user_id = $_POST['user_id'];
    $test_name = $_POST['test_name'];
    $test_date = $_POST['test_date'];
    $result_value = $_POST['result_value'];
    $reference_range = $_POST['reference_range'];
    $doctor_name = $_POST['doctor_name'];

    // File Upload
    $target_dir = "../uploads/";
    $file_name = basename($_FILES["report_file"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name; // Rename to avoid conflicts
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a PDF
    if ($fileType != "pdf") {
        $message = "Only PDF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk && move_uploaded_file($_FILES["report_file"]["tmp_name"], $target_file)) {
        // Save relative path for DB
        $db_file_path = "uploads/" . time() . "_" . $file_name;

        $stmt = $pdo->prepare("INSERT INTO reports (user_id, test_name, test_date, result_value, reference_range, report_file, doctor_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $test_name, $test_date, $result_value, $reference_range, $db_file_path, $doctor_name])) {
            $message = "Report uploaded successfully!";
        } else {
            $message = "Database error.";
        }
    } else if ($uploadOk) {
        $message = "Error uploading file.";
    }
}

// Fetch Users for Dropdown
$users = $pdo->query("SELECT id, name, voter_id FROM users ORDER BY name")->fetchAll();

// Fetch Recent Reports
$reports = $pdo->query("SELECT r.*, u.name as user_name FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Reports - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Manage Medical Reports</h1>
            <a href="dashboard.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                <p>
                    <?php echo htmlspecialchars($message); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
            <h2 class="text-lg font-bold mb-4">Upload New Report</h2>
            <form action="" method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Select Patient</label>
                    <select name="user_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                        <option value="">-- Select Patient --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name']); ?> (VID:
                                <?php echo htmlspecialchars($user['voter_id']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Test Name</label>
                    <input type="text" name="test_name" required placeholder="e.g. CBC, Lipid Profile"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Test Date</label>
                    <input type="date" name="test_date" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Result Value</label>
                    <input type="text" name="result_value" placeholder="e.g. 110 mg/dL"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reference Range</label>
                    <input type="text" name="reference_range" placeholder="e.g. 70-100 mg/dL"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Doctor Name</label>
                    <input type="text" name="doctor_name" placeholder="Dr. XYZ"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Report PDF</label>
                    <input type="file" name="report_file" accept=".pdf" required
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="md:col-span-2 pt-4">
                    <button type="submit" name="upload"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Upload Report</button>
                </div>
            </form>
        </div>

        <!-- Reports List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <h2 class="text-lg font-bold p-6 bg-gray-50 border-b">Recent Uploads</h2>
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-500 text-sm">
                    <tr>
                        <th class="px-6 py-3">Patient</th>
                        <th class="px-6 py-3">Test</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Result</th>
                        <th class="px-6 py-3">File</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($reports as $report): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($report['user_name']); ?>
                            </td>
                            <td class="px-6 py-4 font-medium">
                                <?php echo htmlspecialchars($report['test_name']); ?>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                <?php echo htmlspecialchars($report['test_date']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($report['result_value']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <a href="../<?php echo htmlspecialchars($report['report_file']); ?>" target="_blank"
                                    class="text-blue-600 hover:underline">View PDF</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>