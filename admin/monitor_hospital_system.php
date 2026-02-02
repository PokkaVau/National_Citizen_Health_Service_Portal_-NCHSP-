<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

// Fetch System Stats
// Total Hospitals, Total Bookings, Critical Stock (hospitals with 0 stock in any blood group)
$totalHospitals = $pdo->query("SELECT COUNT(*) FROM hospitals")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM blood_bookings")->fetchColumn();
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM blood_bookings WHERE status = 'pending'")->fetchColumn();

// Fetch Recent Bookings
$bookings = $pdo->query("
    SELECT bb.*, u.name as user_name, h.name as hospital_name
    FROM blood_bookings bb
    JOIN users u ON bb.user_id = u.id
    JOIN hospitals h ON bb.hospital_id = h.id
    ORDER BY bb.created_at DESC
    LIMIT 20
")->fetchAll();

// Fetch Inventory Overview (Group by Hospital)
$inventory = $pdo->query("
    SELECT h.name as hospital_name, hi.blood_group, hi.quantity
    FROM hospital_inventory hi
    JOIN hospitals h ON hi.hospital_id = h.id
    ORDER BY h.name, hi.blood_group
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor - Admin Portal</title>
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
                        class="flex items-center gap-3 px-4 py-3 bg-slate-800 text-white rounded-lg transition-colors">
                        <i class="fas fa-home w-5"></i> Dashboard
                    </a>
                    <a href="manage_hospitals.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-hospital w-5"></i> Hospitals
                    </a>
                    <a href="manage_representatives.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-user-tie w-5"></i> Representatives
                    </a>
                    <a href="manage_users.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-users w-5"></i> Users
                    </a>
                    <a href="manage_blood_requests.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-hand-holding-medical w-5"></i> Requests
                    </a>
                    <a href="../logout.php"
                        class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-slate-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt w-5"></i> Logout
                    </a>
                </nav>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-slate-50 p-8">
            <div class="max-w-7xl mx-auto space-y-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">System Monitor</h1>
                        <p class="text-slate-500">Overview of Hospital Bookings & Inventory</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wide">Total Hospitals</div>
                        <div class="mt-2 text-3xl font-bold text-slate-900">
                            <?php echo $totalHospitals; ?>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wide">Pending Bookings</div>
                        <div class="mt-2 text-3xl font-bold text-orange-600">
                            <?php echo $pendingBookings; ?>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="text-slate-500 text-sm font-medium uppercase tracking-wide">Total Bookings</div>
                        <div class="mt-2 text-3xl font-bold text-slate-900">
                            <?php echo $totalBookings; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings Table -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-bold text-slate-900">Recent Hospital Bookings</h2>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">User</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Hospital</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Booking</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($bookings as $b):
                                $status_color = match ($b['status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'fulfilled' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 font-medium">
                                        <?php echo htmlspecialchars($b['user_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <?php echo htmlspecialchars($b['hospital_name']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-tint"></i>
                                            <?php echo $b['blood_group']; ?>
                                        </span>
                                        <span class="text-xs text-slate-500 ml-1">x
                                            <?php echo $b['units']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                            <?php echo ucfirst($b['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 text-sm">
                                        <?php echo date('M d, H:i', strtotime($b['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Inventory Snapshot would go here, maybe too much data for one page -->
            </div>
        </main>
    </div>
</body>

</html>