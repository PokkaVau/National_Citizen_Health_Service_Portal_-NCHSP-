<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";
$error = "";

// Handle Approve/Reject/Delete
if (isset($_POST['action'])) {
    $admin_id = $_POST['admin_id'];
    $action = $_POST['action'];

    try {
        $pdo->beginTransaction();

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$admin_id]);
            $message = "Doctor deleted successfully.";
        } elseif ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE doctors SET status = 'approved' WHERE admin_id = ?");
            $stmt->execute([$admin_id]);
            $message = "Doctor approved successfully.";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE doctors SET status = 'rejected' WHERE admin_id = ?");
            $stmt->execute([$admin_id]);
            $message = "Doctor rejected.";
        }

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error performing action: " . $e->getMessage();
    }
}

// Fetch Doctors with Admin info
$stmt = $pdo->query("SELECT d.*, a.username FROM doctors d JOIN admins a ON d.admin_id = a.id ORDER BY FIELD(d.status, 'pending', 'approved', 'rejected'), d.name ASC");
$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - Admin</title>
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">stethoscope</span> Manage Doctors
            </h2>
            <div class="flex gap-3">
                <a href="name_requests.php"
                    class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-indigo-600">badge</span> Requests
                </a>
                <a href="add_doctor.php"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined">add</span> Add Doctor
                </a>
            </div>
        </div>

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
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Doctor</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Specialization</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Username</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($doctors) > 0): ?>
                        <?php foreach ($doctors as $doc): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold overflow-hidden">
                                            <?php if (!empty($doc['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars(str_replace('../', '../', $doc['profile_picture'])); ?>"
                                                    class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($doc['name'], 4, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($doc['name']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($doc['specialization']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @<?php echo htmlspecialchars($doc['username']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($doc['status'] == 'approved'): ?>
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium border border-green-200">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            Approved
                                        </span>
                                    <?php elseif ($doc['status'] == 'rejected'): ?>
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium border border-red-200">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                            Rejected
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium border border-yellow-200">
                                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="" method="post" class="inline-flex items-center gap-2">
                                        <input type="hidden" name="admin_id" value="<?php echo $doc['admin_id']; ?>">

                                        <?php if ($doc['status'] == 'pending'): ?>
                                            <button type="submit" name="action" value="approve"
                                                class="text-green-600 hover:bg-green-50 p-2 rounded-lg transition-colors border border-green-200"
                                                title="Approve">
                                                <span class="material-symbols-outlined">check</span>
                                            </button>
                                            <button type="submit" name="action" value="reject"
                                                class="text-orange-500 hover:bg-orange-50 p-2 rounded-lg transition-colors border border-orange-200"
                                                title="Reject">
                                                <span class="material-symbols-outlined">block</span>
                                            </button>
                                        <?php endif; ?>

                                        <button type="submit" name="action" value="delete"
                                            onclick="return confirm('Delete this doctor? This will remove all their schedule and appointments.');"
                                            class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors border border-red-200"
                                            title="Delete">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No doctors found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>