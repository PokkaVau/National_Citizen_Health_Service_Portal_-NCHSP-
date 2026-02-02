<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";
$error = "";

// Handle Add Assistant
if (isset($_POST['add_assistant'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $mobile = trim($_POST['mobile']);
    $doctor_id = !empty($_POST['doctor_id']) ? $_POST['doctor_id'] : null;

    try {
        $pdo->beginTransaction();

        // 1. Create Admin Account
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'assistant')");
        $stmt->execute([$username, $hashed_password]);
        $admin_id = $pdo->lastInsertId();

        // 2. Create Assistant Profile
        $stmt = $pdo->prepare("INSERT INTO assistants (admin_id, doctor_id, name, mobile) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, $doctor_id, $name, $mobile]);

        $pdo->commit();
        $message = "Assistant added successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            $error = "Username already exists!";
        } else {
            $error = "Error adding assistant: " . $e->getMessage();
        }
    }
}

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

// Fetch Doctors for Dropdown
$doctors_stmt = $pdo->query("SELECT id, name FROM doctors ORDER BY name ASC");
$doctors = $doctors_stmt->fetchAll();

// Fetch Assistants
$stmt = $pdo->query("
    SELECT ast.*, adm.username, d.name as doctor_name 
    FROM assistants ast 
    JOIN admins adm ON ast.admin_id = adm.id 
    LEFT JOIN doctors d ON ast.doctor_id = d.id 
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

        <!-- Add Assistant Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Add New Assistant</h3>
            <form action="" method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                    <input type="text" name="mobile" required pattern="[0-9]{11}" minlength="11" maxlength="11"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username (Login ID)</label>
                    <input type="text" name="username" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign to Doctor (Optional)</label>
                    <select name="doctor_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- General Assistant (Unassigned) --</option>
                        <?php foreach ($doctors as $doc): ?>
                            <option value="<?php echo $doc['id']; ?>">
                                <?php echo htmlspecialchars($doc['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" name="add_assistant"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                        Add Assistant
                    </button>
                </div>
            </form>
        </div>

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
                                <?php echo $ast['doctor_name'] ? htmlspecialchars($ast['doctor_name']) : '<span class="text-gray-400 italic">General Assistant</span>'; ?>
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