<?php
require('../config/db.php');
require('../auth_session.php');
check_doctor_login();

$admin_id = $_SESSION['admin_id'];
$message = "";
$error = "";

// Get Doctor ID
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

// Handle Add Assistant
if (isset($_POST['add_assistant'])) {
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validation
    if (empty($name) || empty($mobile) || empty($username) || empty($password)) {
        $error = "All fields are required!";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Create Admin User
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'assistant')");
            $stmt->execute([$username, $hashed_password]);
            $new_admin_id = $pdo->lastInsertId();

            // 2. Create Assistant Profile linked to Doctor
            $stmt = $pdo->prepare("INSERT INTO assistants (admin_id, doctor_id, name, mobile) VALUES (?, ?, ?, ?)");
            $stmt->execute([$new_admin_id, $doctor_id, $name, $mobile]);

            $pdo->commit();
            $message = "Assistant registered successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "Username already exists.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Handle Delete Assistant
if (isset($_POST['delete_assistant'])) {
    $assistant_id = $_POST['assistant_id'];
    // Confirm ownership
    $stmt = $pdo->prepare("SELECT admin_id FROM assistants WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$assistant_id, $doctor_id]);
    $assist = $stmt->fetch();

    if ($assist) {
        $del_admin_id = $assist['admin_id'];
        // Deleting from admins should cascade delete from assistants
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$del_admin_id]);
        $message = "Assistant removed.";
    }
}

// Fetch My Assistants
$stmt = $pdo->prepare("SELECT a.*, adm.username FROM assistants a JOIN admins adm ON a.admin_id = adm.id WHERE a.doctor_id = ?");
$stmt->execute([$doctor_id]);
$assistants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assistants - Doctor Portal</title>
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

<body class="bg-gray-50 flex font-inter">
    <?php include 'layout/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-7xl mx-auto h-screen overflow-y-auto">
        <div class="p-8">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Manage Assistants</h1>
            </header>

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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Add Assistant Form -->
                <div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">person_add</span> Register Assistant
                        </h2>
                        <form action="" method="post" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                                <input type="text" name="name" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Mobile</label>
                                <input type="text" name="mobile" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Username (for
                                    login)</label>
                                <input type="text" name="username" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                                <input type="password" name="password" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <button type="submit" name="add_assistant"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg transition-colors">
                                create
                            </button>
                        </form>
                    </div>
                </div>

                <!-- List Assistants -->
                <div>
                    <h2 class="text-lg font-bold mb-4">My Assistants</h2>
                    <div class="space-y-4">
                        <?php if (count($assistants) > 0): ?>
                            <?php foreach ($assistants as $asst): ?>
                                <div
                                    class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                            <span class="material-symbols-outlined">badge</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">
                                                <?php echo htmlspecialchars($asst['name']); ?>
                                            </p>
                                            <p class="text-xs text-slate-500">@
                                                <?php echo htmlspecialchars($asst['username']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <form action="" method="post" onsubmit="return confirm('Remove this assistant?');">
                                        <input type="hidden" name="assistant_id" value="<?php echo $asst['id']; ?>">
                                        <button type="submit" name="delete_assistant"
                                            class="text-red-400 hover:text-red-600 p-2">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center p-8 bg-indigo-50/50 rounded-xl border border-dashed border-indigo-200">
                                <p class="text-slate-500 text-sm">No assistants added.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>