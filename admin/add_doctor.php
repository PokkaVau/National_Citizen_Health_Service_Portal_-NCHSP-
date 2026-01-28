<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";

if (isset($_POST['add_doctor'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $specialization = trim($_POST['specialization']);

    if (empty($name) || empty($username) || empty($password) || empty($specialization)) {
        $message = "All fields are required!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // 1. Insert into admins table (role = 'doctor')
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'doctor')");
            $stmt->execute([$username, $hashed_password]);
            $admin_id = $pdo->lastInsertId();

            // 2. Insert into doctors table
            $stmt = $pdo->prepare("INSERT INTO doctors (admin_id, name, specialization) VALUES (?, ?, ?)");
            $stmt->execute([$admin_id, $name, $specialization]);

            $pdo->commit();
            $message = "Doctor added successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) { // Duplicate entry
                $message = "Username already exists!";
            } else {
                $message = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - NCHSP Admin</title>
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

<body class="bg-slate-100">
    <div class="min-h-screen flex">
        <!-- Admin Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col p-4">
            <div class="flex items-center gap-3 px-2 py-4 mb-6">
                <span class="material-symbols-outlined text-3xl text-blue-400">admin_panel_settings</span>
                <h1 class="text-xl font-bold">Admin Portal</h1>
            </div>
            <nav class="space-y-2">
                <a href="dashboard.php"
                    class="flex items-center gap-3 px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined">dashboard</span> Dashboard
                </a>
                <a href="add_doctor.php"
                    class="flex items-center gap-3 px-3 py-3 rounded-lg bg-blue-600 text-white active">
                    <span class="material-symbols-outlined">stethoscope</span> Add Doctor
                </a>
                <a href="manage_reports.php"
                    class="flex items-center gap-3 px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined">description</span> Manage Reports
                </a>
                <a href="manage_camps.php"
                    class="flex items-center gap-3 px-3 py-3 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined">campaign</span> Health Camps
                </a>
                <a href="../logout.php"
                    class="flex items-center gap-3 px-3 py-3 rounded-lg text-red-400 hover:bg-slate-800 mt-10 transition-colors">
                    <span class="material-symbols-outlined">logout</span> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold text-slate-800 mb-6">Add New Doctor</h1>

                <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
                    <?php if ($message): ?>
                        <div
                            class="p-4 mb-6 rounded-lg <?php echo strpos($message, 'Error') !== false || strpos($message, 'exists') !== false ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Doctor's Name</label>
                                <input type="text" name="name" required placeholder="e.g. Dr. Sarah Smith"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Specialization</label>
                                <input type="text" name="specialization" required placeholder="e.g. Cardiologist"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Username (Login ID)</label>
                                <input type="text" name="username" required placeholder="e.g. drsarah"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                                <input type="password" name="password" required placeholder="••••••••"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" name="add_doctor"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined icon-sm">add</span>
                                Add Doctor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>