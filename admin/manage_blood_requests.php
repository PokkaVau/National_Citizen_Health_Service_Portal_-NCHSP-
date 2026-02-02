<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";

// Handle Status Updates
if (isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['update_status'];
    $handled_by = $_SESSION['admin_id'];

    try {
        $stmt = $pdo->prepare("UPDATE blood_requests SET status = ?, handled_by = ? WHERE id = ?");
        $stmt->execute([$status, $handled_by, $request_id]);
        $message = "Request status updated successfully!";

        // Notify User
        require_once '../config/notifications.php';
        // Get User ID
        $reqUserStmt = $pdo->prepare("SELECT user_id, patient_name FROM blood_requests WHERE id = ?");
        $reqUserStmt->execute([$request_id]);
        $reqUser = $reqUserStmt->fetch();

        if ($reqUser) {
            $msgType = $status == 'approved' ? 'success' : 'danger';
            createNotification($pdo, $reqUser['user_id'], 'user', "Blood request for " . $reqUser['patient_name'] . " was " . $status, $msgType, '/dbms/dashboard.php');
        }
    } catch (PDOException $e) {
        $message = "Error updating status: " . $e->getMessage();
    }
}

// Fetch Pending & Handled Requests
$stmt = $pdo->query("
    SELECT br.*, u.name as requestor_name, adm.username as handler_name
    FROM blood_requests br
    JOIN users u ON br.user_id = u.id 
    LEFT JOIN admins adm ON br.handled_by = adm.id
    ORDER BY br.created_at DESC
");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blood Requests - Admin Dashboard</title>
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
                    <a href="manage_doctors.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-user-md w-5"></i> Doctors
                    </a>
                    <a href="manage_reports.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-file-medical w-5"></i> Reports
                    </a>
                    <a href="manage_blood_requests.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-hand-holding-medical w-5"></i> Blood Requests
                    </a>
                    <a href="donor_access_logs.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
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
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Blood Requests</h1>
                        <p class="text-slate-500">Manage blood donation requests</p>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <span class="block sm:inline">
                            <?php echo $message; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Requests Table -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Requestor</th>
                                <th class="px-6 py-4">Patient Info</th>
                                <th class="px-6 py-4">Required</th>
                                <th class="px-6 py-4">Contact</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($requests as $req):
                                $status_color = match ($req['status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'fulfilled' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">
                                            <?php echo htmlspecialchars($req['requestor_name']); ?>
                                        </div>
                                        <div class="text-xs text-slate-500">ID:
                                            <?php echo $req['user_id']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">
                                            <?php echo htmlspecialchars($req['patient_name']); ?>
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            <?php echo htmlspecialchars($req['hospital']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-tint"></i>
                                            <?php echo $req['blood_group']; ?>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            <?php echo $req['units_needed']; ?> Units
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900">
                                            <?php echo htmlspecialchars($req['contact_person']); ?>
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            <?php echo htmlspecialchars($req['contact_number']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                            <?php echo ucfirst($req['status']); ?>
                                        </span>
                                        <?php if ($req['handler_name']): ?>
                                            <div class="text-xs text-slate-500 mt-1">
                                                By: @<?php echo htmlspecialchars($req['handler_name']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="" method="POST" class="inline-flex gap-2">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <?php if ($req['status'] === 'pending'): ?>
                                                <button type="submit" name="update_status" value="approved"
                                                    class="p-1 px-3 bg-green-50 text-green-600 hover:bg-green-100 rounded-lg text-xs font-semibold transition-colors">
                                                    Accept
                                                </button>
                                                <button type="submit" name="update_status" value="rejected"
                                                    class="p-1 px-3 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-semibold transition-colors">
                                                    Reject
                                                </button>
                                            <?php else: ?>
                                                <button type="button" disabled
                                                    class="text-slate-400 text-sm cursor-not-allowed">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
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