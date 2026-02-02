<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once 'auth_rep.php';

if (!isset($_SESSION['hospital_id'])) {
    // Should have been set by auth_rep.php, but if not:
    // Try to re-fetch or redirect
    header("Location: ../login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id'];

// Get Hospital Info
$stmt = $pdo->prepare("SELECT * FROM hospitals WHERE id = ?");
$stmt->execute([$hospital_id]);
$hospital = $stmt->fetch();

// Get Stats
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM blood_bookings WHERE hospital_id = $hospital_id AND status = 'pending'")->fetchColumn();
$todayBookings = $pdo->query("SELECT COUNT(*) FROM blood_bookings WHERE hospital_id = $hospital_id AND DATE(created_at) = CURDATE()")->fetchColumn();

// Get Recent Bookings
$recentBookings = $pdo->query("
    SELECT bb.*, u.name as user_name 
    FROM blood_bookings bb 
    JOIN users u ON bb.user_id = u.id 
    WHERE bb.hospital_id = $hospital_id 
    ORDER BY bb.created_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard - NCHSP</title>
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
                    <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hospital text-white"></i>
                    </div>
                    <span class="text-sm font-bold truncate">
                        <?php echo htmlspecialchars($hospital['name']); ?>
                    </span>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php"
                        class="flex items-center gap-3 px-4 py-3 bg-red-800 text-white rounded-lg transition-colors">
                        <i class="fas fa-home w-5"></i> Dashboard
                    </a>
                    <a href="inventory.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-boxes w-5"></i> Inventory
                    </a>
                    <a href="bookings.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-file-medical w-5"></i> Bookings
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
                        <h1 class="text-2xl font-bold text-slate-900">Hospital Dashboard</h1>
                        <p class="text-slate-500">Welcome, Representative</p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Pending Bookings</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2">
                                    <?php echo $pendingBookings; ?>
                                </p>
                            </div>
                            <div
                                class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Today's Bookings</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2">
                                    <?php echo $todayBookings; ?>
                                </p>
                            </div>
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                <i class="fas fa-calendar-day text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-slate-900">Recent Bookings</h2>
                        <a href="bookings.php" class="text-sm text-blue-600 hover:underline">View All</a>
                    </div>
                    <?php if (count($recentBookings) > 0): ?>
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">User</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Details</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($recentBookings as $b): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <?php echo htmlspecialchars($b['user_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            <span class="font-bold text-red-600">
                                                <?php echo $b['blood_group']; ?>
                                            </span>
                                            (
                                            <?php echo $b['units']; ?> units)
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                <?php echo ucfirst($b['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-8 text-center text-slate-500">No bookings yet.</div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>

</html>