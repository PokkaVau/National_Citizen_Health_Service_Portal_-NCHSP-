<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

// Quick Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_reports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$total_camps = $pdo->query("SELECT COUNT(*) FROM health_camps")->fetchColumn();

// Additional stats for enhanced dashboard
$recent_users = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$active_camps = $pdo->query("SELECT COUNT(*) FROM health_camps WHERE camp_date >= CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .glass-sidebar {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        .dark .glass-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .nav-item {
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-item::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.3s ease;
        }

        .nav-item:hover::after {
            width: 100%;
        }

        .nav-item.active::after {
            width: 100%;
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="min-h-screen flex">
        <!-- Admin Sidebar - Enhanced -->
        <aside class="w-64 glass-sidebar text-white flex flex-col p-6 shadow-xl">
            <div class="flex items-center gap-4 px-2 py-4 mb-8">
                <div class="relative">
                    <div
                        class="size-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="material-symbols-outlined text-2xl">admin_panel_settings</span>
                    </div>
                    <span
                        class="absolute -top-1 -right-1 size-3 bg-green-400 rounded-full border-2 border-slate-900"></span>
                </div>
                <div>
                    <h1
                        class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                        Admin Portal</h1>
                    <p class="text-xs text-slate-400 mt-1">NCHSP Administration</p>
                </div>
            </div>

            <nav class="space-y-2 flex-1">
                <div class="px-2 mb-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">Main</p>
                </div>
                <a href="dashboard.php"
                    class="nav-item active flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-blue-600/20 to-purple-600/20 text-white border border-blue-500/30 group">
                    <span class="material-symbols-outlined text-blue-400">dashboard</span>
                    <span class="font-medium">Dashboard</span>
                    <span class="ml-auto size-2 bg-blue-400 rounded-full pulse-dot"></span>
                </a>

                <div class="px-2 mb-4 mt-6">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">Management</p>
                </div>

                <a href="manage_users.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-blue-400 transition-colors">group</span>
                    <span class="font-medium">Manage Users</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="add_doctor.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-green-400 transition-colors">person_add</span>
                    <span class="font-medium">Add Doctor</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="manage_doctors.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-purple-400 transition-colors">stethoscope</span>
                    <span class="font-medium">Manage Doctors</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="manage_assistants.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-amber-400 transition-colors">badge</span>
                    <span class="font-medium">Manage Assistants</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="manage_reports.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-emerald-400 transition-colors">description</span>
                    <span class="font-medium">Manage Reports</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="manage_blood_requests.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-red-400 transition-colors">bloodtype</span>
                    <span class="font-medium">Blood Requests</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="manage_hospitals.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-pink-400 transition-colors">local_hospital</span>
                    <span class="font-medium">Hospitals</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="manage_representatives.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-cyan-400 transition-colors">admin_meds</span>
                    <span class="font-medium">Representatives</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="monitor_hospital_system.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-teal-400 transition-colors">monitor_heart</span>
                    <span class="font-medium">System Monitor</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <a href="manage_camps.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800/50 hover:text-white transition-all duration-300 group">
                    <span
                        class="material-symbols-outlined text-lg group-hover:text-rose-400 transition-colors">campaign</span>
                    <span class="font-medium">Health Camps</span>
                    <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                </a>

                <div class="pt-8 mt-8 border-t border-slate-800">
                    <div class="px-4 py-3 bg-slate-800/30 rounded-xl mb-4">
                        <p class="text-xs text-slate-400">Admin ID:
                            <?php echo htmlspecialchars($_SESSION['admin_id'] ?? 'N/A'); ?>
                        </p>
                        <p class="text-sm text-white font-medium">Administrator</p>
                    </div>

                    <a href="../logout.php"
                        class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-300 group">
                        <span class="material-symbols-outlined text-lg">logout</span>
                        <span class="font-medium">Logout</span>
                        <span class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content - Enhanced -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Dashboard Overview</h1>
                    <p class="text-slate-600">Welcome back, Administrator. Here's what's happening with your portal
                        today.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button id="notificationBtn"
                            class="p-2 rounded-full bg-white shadow-sm hover:shadow-md transition-shadow">
                            <span class="material-symbols-outlined text-slate-600">notifications</span>
                        </button>
                        <span
                            class="notification-dot hidden absolute top-0 right-0 size-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl shadow-sm">
                        <div
                            class="size-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            A
                        </div>
                        <span class="font-medium text-slate-800">Admin</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid - Enhanced -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card glass-card p-6 rounded-2xl hover-lift">
                    <div class="flex items-start justify-between mb-6">
                        <div class="p-3 bg-gradient-to-br from-blue-500/10 to-blue-600/10 rounded-xl">
                            <span class="material-symbols-outlined text-2xl text-blue-600">group</span>
                        </div>
                        <span
                            class="text-xs font-medium px-2 py-1 bg-blue-100 text-blue-700 rounded-full">+<?php echo $recent_users; ?>
                            this week</span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-2">Total Citizens</p>
                        <div class="flex items-end justify-between">
                            <p class="text-3xl font-bold text-slate-800"><?php echo number_format($total_users); ?></p>
                            <span class="text-green-500 text-sm font-medium flex items-center">
                                <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                                12%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stat-card glass-card p-6 rounded-2xl hover-lift">
                    <div class="flex items-start justify-between mb-6">
                        <div class="p-3 bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 rounded-xl">
                            <span class="material-symbols-outlined text-2xl text-emerald-600">lab_panel</span>
                        </div>
                        <span
                            class="text-xs font-medium px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full">Pending:
                            12</span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-2">Test Reports</p>
                        <div class="flex items-end justify-between">
                            <p class="text-3xl font-bold text-slate-800"><?php echo number_format($total_reports); ?>
                            </p>
                            <span class="text-blue-500 text-sm font-medium flex items-center">
                                <span class="material-symbols-outlined text-sm mr-1">insights</span>
                                8%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stat-card glass-card p-6 rounded-2xl hover-lift">
                    <div class="flex items-start justify-between mb-6">
                        <div class="p-3 bg-gradient-to-br from-purple-500/10 to-purple-600/10 rounded-xl">
                            <span class="material-symbols-outlined text-2xl text-purple-600">location_city</span>
                        </div>
                        <span
                            class="text-xs font-medium px-2 py-1 bg-purple-100 text-purple-700 rounded-full"><?php echo $active_camps; ?>
                            active</span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-2">Health Camps</p>
                        <div class="flex items-end justify-between">
                            <p class="text-3xl font-bold text-slate-800"><?php echo number_format($total_camps); ?></p>
                            <span class="text-amber-500 text-sm font-medium flex items-center">
                                <span class="material-symbols-outlined text-sm mr-1">event_available</span>
                                5%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stat-card glass-card p-6 rounded-2xl hover-lift">
                    <div class="flex items-start justify-between mb-6">
                        <div class="p-3 bg-gradient-to-br from-amber-500/10 to-amber-600/10 rounded-xl">
                            <span class="material-symbols-outlined text-2xl text-amber-600">verified_user</span>
                        </div>
                        <span
                            class="text-xs font-medium px-2 py-1 bg-amber-100 text-amber-700 rounded-full">Secure</span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-2">System Health</p>
                        <div class="flex items-end justify-between">
                            <p class="text-3xl font-bold text-slate-800">99.9%</p>
                            <span class="text-green-500 text-sm font-medium flex items-center">
                                <span class="material-symbols-outlined text-sm mr-1">check_circle</span>
                                Online
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Quick Actions - Enhanced -->
                <div class="lg:col-span-3">
                    <div class="glass-card p-6 rounded-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-slate-800">Quick Actions</h3>
                            <span class="text-sm text-slate-500">Most frequent tasks</span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">


                            <a href="manage_camps.php"
                                class="flex flex-col items-center justify-center p-6 rounded-xl border-2 border-dashed border-slate-200 hover:border-purple-300 hover:bg-purple-50/50 transition-all duration-300 group hover-lift">
                                <div
                                    class="size-14 bg-gradient-to-br from-purple-500/10 to-purple-600/10 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-2xl text-purple-600">add_location</span>
                                </div>
                                <span class="text-sm font-medium text-slate-700">Add Camp</span>
                                <span class="text-xs text-slate-500 mt-1">Organize health camp</span>
                            </a>

                            <a href="add_doctor.php"
                                class="flex flex-col items-center justify-center p-6 rounded-xl border-2 border-dashed border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50 transition-all duration-300 group hover-lift">
                                <div
                                    class="size-14 bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-2xl text-emerald-600">person_add</span>
                                </div>
                                <span class="text-sm font-medium text-slate-700">Add Doctor</span>
                                <span class="text-xs text-slate-500 mt-1">Register new doctor</span>
                            </a>

                            <a href="manage_users.php"
                                class="flex flex-col items-center justify-center p-6 rounded-xl border-2 border-dashed border-slate-200 hover:border-amber-300 hover:bg-amber-50/50 transition-all duration-300 group hover-lift">
                                <div
                                    class="size-14 bg-gradient-to-br from-amber-500/10 to-amber-600/10 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-2xl text-amber-600">group_add</span>
                                </div>
                                <span class="text-sm font-medium text-slate-700">Add User</span>
                                <span class="text-xs text-slate-500 mt-1">Register new citizen</span>
                            </a>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Footer Note -->
            <div class="mt-8 text-center">
                <p class="text-sm text-slate-500">
                    © 2023 NCHSP Admin Portal. Server time: <?php echo date('Y-m-d H:i:s'); ?>
                </p>
            </div>
        </main>
    </div>

    <script>
        // Add some subtle animations
        document.addEventListener('DOMContentLoaded', function () {
            // Add animation to stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in-up');
            });
        });
    </script>
    <script src="../js/notifications.js"></script>
</body>

</html>