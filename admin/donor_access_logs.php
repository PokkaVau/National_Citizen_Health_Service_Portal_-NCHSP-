<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

// Fetch Logs
$stmt = $pdo->query("
    SELECT l.*, u.name as user_name, br.patient_name, br.blood_group
    FROM donor_access_logs l
    JOIN users u ON l.user_id = u.id
    JOIN blood_requests br ON l.request_id = br.id
    ORDER BY l.accessed_at DESC
");
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Access Logs - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-heartbeat text-white"></i>
                    </div>
                    <span class="text-lg font-bold">Admin Portal</span>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-home w-5"></i> Dashboard
                    </a>
                    <a href="manage_users.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-users w-5"></i> Users
                    </a>
                    <a href="manage_blood_requests.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-hand-holding-medical w-5"></i> Blood Requests
                    </a>
                    <a href="donor_access_logs.php"
                        class="flex items-center gap-3 px-4 py-3 text-blue-400 bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-history w-5"></i> Access Logs
                    </a>
                    <a href="../logout.php"
                        class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-slate-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt w-5"></i> Logout
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-8">
            <div class="max-w-7xl mx-auto space-y-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Donor Information Access Logs</h1>
                    <p class="text-slate-500">Track who viewed donor contact information</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">For Patient</th>
                                <th class="px-6 py-4">Blood Group</th>
                                <th class="px-6 py-4">Accessed At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">
                                            <?php echo htmlspecialchars($log['user_name']); ?>
                                        </div>
                                        <div class="text-xs text-slate-500">ID:
                                            <?php echo $log['user_id']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700">
                                        <?php echo htmlspecialchars($log['patient_name']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <?php echo htmlspecialchars($log['blood_group']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 text-sm">
                                        <?php echo date('M d, Y h:i A', strtotime($log['accessed_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>