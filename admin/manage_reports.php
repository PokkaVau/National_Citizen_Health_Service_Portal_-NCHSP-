<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";

// Reports are uploaded by Doctors/Assistants only. Admin can only view.

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