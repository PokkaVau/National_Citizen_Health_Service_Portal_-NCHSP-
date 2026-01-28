<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";
$error = "";

// Handle Delete
if (isset($_POST['delete_assistant'])) {
    $admin_id = $_POST['admin_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $message = "Assistant deleted successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting assistant: " . $e->getMessage();
    }
}

// Fetch Assistants
$stmt = $pdo->query("
    SELECT ast.*, adm.username, d.name as doctor_name 
    FROM assistants ast 
    JOIN admins adm ON ast.admin_id = adm.id 
    JOIN doctors d ON ast.doctor_id = d.id 
    ORDER BY ast.name ASC
");
$assistants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assistants - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800">Admin Panel</h1>
            <div class="flex gap-4">
                <a href="dashboard.php" class="text-gray-600 hover:text-blue-600">Dashboard</a>
                <a href="../logout.php" class="text-red-500 hover:text-red-700">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-600">badge</span> Manage Assistants
        </h2>

        <?php if ($message): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Assistant</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Assigned To</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Mobile</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($assistants as $ast): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    <?php echo htmlspecialchars($ast['name']); ?>
                                </div>
                                <div class="text-xs text-gray-500">@
                                    <?php echo htmlspecialchars($ast['username']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($ast['doctor_name']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo htmlspecialchars($ast['mobile']); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="" method="post" onsubmit="return confirm('Delete this assistant?');">
                                    <input type="hidden" name="admin_id" value="<?php echo $ast['admin_id']; ?>">
                                    <button type="submit" name="delete_assistant"
                                        class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors"
                                        title="Delete Assistant">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>