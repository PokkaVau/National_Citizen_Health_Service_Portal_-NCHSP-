<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";
$error = "";

// Handle Actions
if (isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    // Get request details
    $stmt = $pdo->prepare("SELECT * FROM doctor_name_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();

    if ($request && $request['status'] == 'pending') {
        if ($action == 'approve') {
            try {
                $pdo->beginTransaction();

                // Update Doctor's Name
                $stmt = $pdo->prepare("UPDATE doctors SET name = ? WHERE id = ?");
                $stmt->execute([$request['requested_name'], $request['doctor_id']]);

                // Also update User or Admin table if necessary? 
                // Currently 'doctors' name is separate from 'admins' username. 
                // But wait, where is 'name' stored? In 'doctors' table. 
                // Admin table only has 'username'. So we are good.

                // Update Request Status
                $stmt = $pdo->prepare("UPDATE doctor_name_requests SET status = 'approved' WHERE id = ?");
                $stmt->execute([$request_id]);

                $pdo->commit();
                $message = "Request approved. Doctor's name updated.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error approving request: " . $e->getMessage();
            }
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE doctor_name_requests SET status = 'rejected' WHERE id = ?");
            if ($stmt->execute([$request_id])) {
                $message = "Request rejected.";
            } else {
                $error = "Error rejecting request.";
            }
        }
    }
}

// Fetch Pending Requests
$stmt = $pdo->query("
    SELECT r.*, d.name as current_db_name, d.profile_picture 
    FROM doctor_name_requests r 
    JOIN doctors d ON r.doctor_id = d.id 
    WHERE r.status = 'pending' 
    ORDER BY r.created_at ASC
");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Name Change Requests - Admin</title>
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
        <div class="flex items-center gap-4 mb-6">
            <a href="manage_doctors.php" class="p-2 rounded-lg bg-white border hover:bg-gray-50 text-gray-600">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">badge</span> Name Change Requests
            </h2>
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

        <?php if (count($requests) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($requests as $req): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="size-12 rounded-full bg-blue-50 flex items-center justify-center overflow-hidden">
                                <?php if (!empty($req['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($req['profile_picture']); ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-blue-400">person</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Current Name</p>
                                <h3 class="font-bold text-gray-900">
                                    <?php echo htmlspecialchars($req['current_db_name']); ?>
                                </h3>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Requested Name</p>
                            <p class="text-lg font-bold text-indigo-700">
                                <?php echo htmlspecialchars($req['requested_name']); ?>
                            </p>
                            <p class="text-xs text-gray-400 mt-2">Requested:
                                <?php echo date('M d, Y h:i A', strtotime($req['created_at'])); ?>
                            </p>
                        </div>

                        <div class="flex gap-3">
                            <form action="" method="post" class="flex-1">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <button type="submit" name="action" value="approve"
                                    class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                    Approve
                                </button>
                            </form>
                            <form action="" method="post" class="flex-1">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <button type="submit" name="action" value="reject"
                                    class="w-full py-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg font-medium transition-colors">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <div class="inline-flex size-16 rounded-full bg-gray-50 items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl text-gray-300">inbox</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No Pending Requests</h3>
                <p class="text-gray-500 mt-1">There are no name change requests to review.</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>