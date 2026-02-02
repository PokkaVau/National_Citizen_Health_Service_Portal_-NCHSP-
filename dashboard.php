<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];

// 1. Fetch User Vitals
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$first_name = explode(' ', $user['name'])[0];

// 2. Fetch Diagnostics
$stmt = $pdo->prepare("SELECT * FROM diagnostics WHERE user_id = ?");
$stmt->execute([$user_id]);
$diagnostics = $stmt->fetchAll();

// 3. Fetch Medications
$stmt = $pdo->prepare("SELECT * FROM medications WHERE user_id = ?");
$stmt->execute([$user_id]);
$medications = $stmt->fetchAll();

// 4. Fetch Reminders
$stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? ORDER BY time ASC");
$stmt->execute([$user_id]);
$reminders = $stmt->fetchAll();

// 5. Fetch Recent Reports
$stmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY test_date DESC LIMIT 3");
$stmt->execute([$user_id]);
$reports = $stmt->fetchAll();

// 6. Fetch Upcoming Appointments
$stmt = $pdo->prepare("
    SELECT a.*, d.name as doctor_name, d.specialization, d.profile_picture 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = ? AND a.appointment_date >= CURDATE() 
    ORDER BY a.appointment_date ASC 
    LIMIT 3
");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll();

// 6b. Fetch Recent Unreviewed Completed Appointment (Shortcut)
$stmt = $pdo->prepare("
    SELECT a.*, d.name as doctor_name 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = ? 
    AND a.status = 'completed' 
    AND NOT EXISTS (SELECT 1 FROM doctor_reviews dr WHERE dr.appointment_id = a.id)
    ORDER BY a.appointment_date DESC 
    LIMIT 1
");
$stmt->execute([$user_id]);
$unreviewed_appt = $stmt->fetch();

// 7. Fetch Blood Requests
try {
    $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $blood_requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $blood_requests = [];
}

// 8. Fetch Hospital Bookings
try {
    $stmt = $pdo->prepare("
        SELECT bb.*, h.name as hospital_name 
        FROM blood_bookings bb 
        JOIN hospitals h ON bb.hospital_id = h.id 
        WHERE bb.user_id = ? 
        ORDER BY bb.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $hospital_bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $hospital_bookings = [];
}

// 9. Check if Hospital Representative - REMOVED (Users are not linked to reps)
$is_rep = false;

// 7. Calculate Health Score
$health_score = 70; // Base score
// BMI Calculation
$height_m = $user['height'] / 100;
$bmi = 0;
if ($height_m > 0) {
    $bmi = $user['weight'] / ($height_m * $height_m);
}

// BMI Points (Max 20)
if ($bmi >= 18.5 && $bmi <= 24.9) {
    $health_score += 20; // Normal weight
} elseif (($bmi >= 17 && $bmi < 18.5) || ($bmi > 24.9 && $bmi <= 29.9)) {
    $health_score += 10; // Slightly under/overweight
} else {
    $health_score += 5; // Needs attention
}

// Profile Points
if (!empty($user['blood_type']))
    $health_score += 5;
if (!empty($user['mobile']))
    $health_score += 5;

// Max cap
if ($health_score > 100)
    $health_score = 100;

// Determine status color
$score_color = 'text-green-500';
if ($health_score < 70)
    $score_color = 'text-yellow-500';
if ($health_score < 50)
    $score_color = 'text-red-500';
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Patient Dashboard - NCHSP</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <style>
        :root {
            --color-primary: #137fec;
            --color-primary-light: #e6f2ff;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #3b82f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), #3b82f6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(19, 127, 236, 0.1);
        }

        .pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }

            70%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .progress-ring {
            stroke-dasharray: 283;
            stroke-dashoffset: 283;
            transition: stroke-dashoffset 1s ease;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, #3b82f6 100%);
        }

        .gradient-success {
            background: linear-gradient(135deg, var(--color-success) 0%, #34d399 100%);
        }

        .gradient-warning {
            background: linear-gradient(135deg, var(--color-warning) 0%, #fbbf24 100%);
        }

        .gradient-danger {
            background: linear-gradient(135deg, var(--color-danger) 0%, #f87171 100%);
        }

        .floating-action {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .reminder-item {
            position: relative;
            padding-left: 2rem;
        }

        .reminder-item::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0.75rem;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            background: var(--color-primary);
        }

        .reminder-item.completed::before {
            background: var(--color-success);
        }

        .medication-progress {
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.05);
        }

        .medication-progress-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease;
        }

        .appointment-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .appointment-card:hover {
            transform: translateX(4px);
            border-left-color: var(--color-primary);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-confirmed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--color-success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--color-warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: var(--color-danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status-completed {
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
            border: 1px solid rgba(107, 114, 128, 0.2);
        }

        .section-header {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--color-primary), transparent);
            border-radius: 3px;
        }

        .notification-dot {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--color-danger);
            animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes ping {

            75%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .quick-action {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .quick-action:hover {
            border-color: var(--color-primary);
            background: rgba(19, 127, 236, 0.05);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-slate-900">
    <div class="relative flex min-h-screen w-full overflow-hidden">
        <!-- Sidebar -->
        <aside
            class="hidden lg:flex w-72 flex-col glass-sidebar shrink-0 h-screen sticky top-0 overflow-y-auto border-r border-slate-200/50">
            <div class="px-6 py-10">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div
                            class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center shadow-lg shadow-primary/30">
                            <i class="fas fa-heartbeat text-white text-2xl"></i>
                        </div>
                        <div
                            class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 rounded-full border-2 border-white flex items-center justify-center">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">
                            HealthPortal
                        </h1>

                        <p class="text-xs text-slate-500 font-medium">National Health Service</p>
                    </div>
                </div>
            </div>

            <nav class="flex flex-col gap-2 px-4">
                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-primary/10 to-blue-500/10 text-primary border border-primary/20"
                    href="dashboard.php">
                    <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">dashboard</span>
                    </div>
                    <p class="text-sm font-semibold">Dashboard</p>
                </a>

                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-100/50 text-slate-600 transition-all duration-300 group"
                    href="profile.php">
                    <div
                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <p class="text-sm font-medium">My Profile</p>
                </a>

                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-100/50 text-slate-600 transition-all duration-300 group"
                    href="reports.php">
                    <div
                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                    <p class="text-sm font-medium">Medical Records</p>
                </a>

                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-100/50 text-slate-600 transition-all duration-300 group"
                    href="ai_summary.php">
                    <div
                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-purple-50 group-hover:text-purple-600 transition-colors">
                        <span class="material-symbols-outlined">smart_toy</span>
                    </div>
                    <p class="text-sm font-medium">AI Health Assistant</p>
                </a>

                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-100/50 text-slate-600 transition-all duration-300 group"
                    href="camps.php">
                    <div
                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">location_on</span>
                    </div>
                    <p class="text-sm font-medium">Health Camps</p>
                </a>

                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-100/50 text-slate-600 transition-all duration-300 group"
                    href="book_appointment.php">
                    <div
                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">calendar_month</span>
                    </div>
                    <p class="text-sm font-medium">Appointments</p>
                </a>

                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-slate-100/50 text-slate-600 transition-all duration-300 group"
                    href="user/nearby_hospitals.php">
                    <div
                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">local_hospital</span>
                    </div>
                    <p class="text-sm font-medium">Hospital Blood Reserve</p>
                </a>



                <div class="mt-8 mb-4 px-4">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Account</div>
                </div>

                <a class="flex items-center gap-3 px-4 py-3.5 rounded-xl hover:bg-red-50 text-red-600 transition-all duration-300 group mt-auto"
                    href="logout.php">
                    <div
                        class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center group-hover:bg-red-100 transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                    </div>
                    <p class="text-sm font-medium">Logout</p>
                </a>
            </nav>

            <!-- User Profile Card -->
            <div
                class="mt-auto mx-4 mb-6 p-4 bg-gradient-to-r from-slate-50 to-white rounded-2xl border border-slate-200/50 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-md">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Avatar"
                                    class="w-full h-full object-cover">
                            <?php else: ?>
                                <div
                                    class="w-full h-full bg-gradient-to-br from-primary to-blue-500 flex items-center justify-center">
                                    <span
                                        class="text-white font-bold text-lg"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div
                            class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white">
                        </div>
                    </div>
                    <div class="overflow-hidden flex-1">
                        <p class="text-sm font-bold text-slate-900 truncate">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </p>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-slate-500">Citizen ID:</span>
                            <span
                                class="text-xs font-mono text-slate-700"><?php echo substr($user['voter_id'], -4); ?></span>
                        </div>
                    </div>
                    <button class="text-slate-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">more_vert</span>
                    </button>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-200/50 flex items-center justify-between">
                    <div class="text-xs text-slate-500">Last login: Today</div>
                    <div class="flex items-center gap-1 text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">
                        <i class="fas fa-shield-check text-xs"></i>
                        <span>Verified</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-y-auto">
            <!-- Header -->
            <header class="glass-card sticky top-0 z-20 border-b border-slate-200/50">
                <div class="flex items-center justify-between px-6 lg:px-8 py-5">
                    <div class="flex items-center gap-4">
                        <button
                            class="lg:hidden text-slate-600 hover:text-primary p-2 rounded-lg hover:bg-slate-100 transition-colors">
                            <span class="material-symbols-outlined text-2xl">menu</span>
                        </button>
                        <div class="hidden lg:block">
                            <h2 class="text-xl font-bold text-slate-900">Health Dashboard</h2>
                            <p class="text-sm text-slate-500">Welcome back,
                                <?php echo htmlspecialchars($first_name); ?>! 👋
                            </p>
                        </div>
                        <div class="lg:hidden">
                            <h1 class="text-lg font-bold text-slate-900 text-black">
                                HealthPortal
                            </h1>
                        </div>

                    </div>

                    <div class="flex items-center gap-6">
                        <!-- Quick Actions -->
                        <div class="hidden md:flex items-center gap-3">
                            <a href="book_appointment.php"
                                class="quick-action flex items-center gap-2 bg-gradient-to-r from-primary to-blue-600 text-gray-600 px-4 py-2.5 rounded-xl font-semibold text-sm transition-all duration-300 hover:shadow-lg hover:shadow-primary/30">
                                <i class="fas fa-calendar-plus"></i>
                                Book Appointment
                            </a>
                            <a href="reports.php"
                                class="quick-action flex items-center gap-2 bg-white text-slate-700 px-4 py-2.5 rounded-xl font-semibold text-sm border border-slate-200 hover:border-primary transition-all duration-300">
                                <i class="fas fa-file-medical"></i>
                                Reports
                            </a>
                        </div>

                        <!-- Notifications -->
                        <div class="relative">
                            <button id="notificationBtn"
                                class="relative p-3 text-slate-500 hover:text-primary hover:bg-slate-100 rounded-xl transition-colors duration-300">
                                <i class="fas fa-bell text-xl"></i>
                                <div class="notification-dot hidden"></div>
                            </button>
                        </div>

                        <!-- Mobile Book Button -->
                        <a href="book_appointment.php"
                            class="md:hidden flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl font-semibold text-sm">
                            <i class="fas fa-calendar-plus"></i>
                        </a>
                    </div>
                </div>
            </header>

            <div class="flex-1 p-4 lg:p-8 space-y-8 max-w-7xl mx-auto w-full">
                <!-- Welcome Section -->
                <div class="rounded-2xl gradient-primary p-6 lg:p-8 text-white relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32">
                    </div>
                    <div
                        class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full -translate-x-20 translate-y-20">
                    </div>

                    <div class="relative z-10">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <div>
                                <h1 class="text-2xl lg:text-3xl font-bold mb-2">Good Morning,
                                    <?php echo htmlspecialchars($first_name); ?>! 👋
                                </h1>
                                <p class="text-blue-100 opacity-90">Here's your health summary for today</p>

                                <div class="flex flex-wrap gap-4 mt-6">
                                    <div
                                        class="flex items-center gap-3 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-xl">
                                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                            <i class="fas fa-heartbeat"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-blue-100">Health Score</p>
                                            <p class="text-lg font-bold"><?php echo $health_score; ?><span
                                                    class="text-sm">/100</span></p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-3 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-xl">
                                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                            <i class="fas fa-prescription-bottle-medical"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-blue-100">Active Meds</p>
                                            <p class="text-lg font-bold"><?php echo count($medications); ?></p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-3 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-xl">
                                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-blue-100">Upcoming</p>
                                            <p class="text-lg font-bold"><?php echo count($appointments); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden lg:flex items-center justify-center">
                                <div class="relative w-40 h-40">
                                    <svg class="w-full h-full" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="rgba(255,255,255,0.2)"
                                            stroke-width="8" />
                                        <circle class="progress-ring" cx="50" cy="50" r="45" fill="none" stroke="white"
                                            stroke-width="8" stroke-linecap="round"
                                            stroke-dashoffset="<?php echo 283 - (283 * $health_score / 100); ?>" />
                                        <text x="50" y="50" text-anchor="middle" dy="0.3em" fill="white" font-size="20"
                                            font-weight="bold"><?php echo $health_score; ?>%</text>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Dashboard Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Review Shortcut (if applicable) -->
                        <?php if ($unreviewed_appt): ?>
                            <div
                                class="glass-card rounded-2xl p-6 border border-yellow-200 bg-yellow-50/50 flex items-center justify-between animate-fade-in">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600">
                                        <span class="material-symbols-outlined text-2xl">rate_review</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900">How was your visit with
                                            <?php echo htmlspecialchars($unreviewed_appt['doctor_name']); ?>?
                                        </h3>
                                        <p class="text-sm text-gray-600">Your feedback helps us improve.</p>
                                    </div>
                                </div>
                                <button
                                    onclick="openReviewModal(<?php echo $unreviewed_appt['id']; ?>, '<?php echo htmlspecialchars($unreviewed_appt['doctor_name']); ?>')"
                                    class="px-5 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-xl shadow-lg shadow-yellow-500/30 transition-all flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">stars</span>
                                    Rate Now
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- Health Metrics -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Weight Card -->
                            <a href="profile.php"
                                class="stat-card glass-card rounded-2xl p-6 border border-slate-200/50 cursor-pointer group">
                                <div class="flex items-start justify-between mb-4">
                                    <div
                                        class="p-3 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-weight-scale text-xl"></i>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-500">Weight</div>
                                </div>
                                <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo $user['weight']; ?><span
                                        class="text-lg text-slate-400 ml-1">kg</span></div>
                                <div class="flex items-center text-xs text-slate-500">
                                    <i class="fas fa-trend-up text-green-500 mr-1"></i>
                                    <span>Normal range</span>
                                </div>
                            </a>

                            <!-- Height Card -->
                            <a href="profile.php"
                                class="stat-card glass-card rounded-2xl p-6 border border-slate-200/50 cursor-pointer group">
                                <div class="flex items-start justify-between mb-4">
                                    <div
                                        class="p-3 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-ruler-vertical text-xl"></i>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-500">Height</div>
                                </div>
                                <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo $user['height']; ?><span
                                        class="text-lg text-slate-400 ml-1">cm</span></div>
                                <div class="flex items-center text-xs text-slate-500">
                                    <i class="fas fa-trend-up text-green-500 mr-1"></i>
                                    <span>Optimal</span>
                                </div>
                            </a>

                            <!-- Blood Type Card -->
                            <a href="profile.php"
                                class="stat-card glass-card rounded-2xl p-6 border border-slate-200/50 cursor-pointer group">
                                <div class="flex items-start justify-between mb-4">
                                    <div
                                        class="p-3 rounded-xl bg-gradient-to-br from-red-50 to-red-100 text-red-600 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-droplet text-xl"></i>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-500">Blood Type</div>
                                </div>
                                <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo $user['blood_type']; ?>
                                </div>
                                <div class="flex items-center text-xs text-slate-500">
                                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                    <span>Universal donor compatible</span>
                                </div>
                            </a>
                        </div>

                        <!-- Appointments Section -->
                        <div class="glass-card rounded-2xl border border-slate-200/50 overflow-hidden">
                            <div class="p-6 border-b border-slate-200/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                            <i class="fas fa-calendar-check text-primary"></i>
                                            Upcoming Appointments
                                        </h3>
                                        <p class="text-sm text-slate-500 mt-1">Your scheduled consultations</p>
                                    </div>
                                    <a href="book_appointment.php"
                                        class="flex items-center gap-2 text-primary hover:text-blue-700 text-sm font-semibold transition-colors">
                                        <span>Book New</span>
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <?php if (count($appointments) > 0): ?>
                                    <div class="space-y-4">
                                        <?php foreach ($appointments as $appt):
                                            $status_class = 'status-' . $appt['status'];
                                            ?>
                                            <div
                                                class="appointment-card p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200/50">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-4">
                                                        <div class="relative">
                                                            <div
                                                                class="w-14 h-14 rounded-xl overflow-hidden border-2 border-white shadow-sm">
                                                                <?php if (!empty($appt['profile_picture'])): ?>
                                                                    <img src="<?php echo htmlspecialchars(str_replace('../', '', $appt['profile_picture'])); ?>"
                                                                        class="w-full h-full object-cover">
                                                                <?php else: ?>
                                                                    <div
                                                                        class="w-full h-full bg-gradient-to-br from-primary to-blue-500 flex items-center justify-center">
                                                                        <i class="fas fa-user-md text-white text-xl"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div
                                                                class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full border border-slate-200 flex items-center justify-center">
                                                                <i class="fas fa-video text-primary text-xs"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-bold text-slate-900">
                                                                <?php echo htmlspecialchars($appt['doctor_name']); ?>
                                                            </h4>
                                                            <p class="text-sm text-slate-500">
                                                                <?php echo htmlspecialchars($appt['specialization']); ?>
                                                            </p>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-xs text-slate-500">
                                                                    <i class="far fa-clock mr-1"></i>
                                                                    <?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?>
                                                                    •
                                                                    <?php echo date('h:i A', strtotime($appt['appointment_date'])); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="<?php echo $status_class; ?> status-badge">
                                                            <?php echo htmlspecialchars($appt['status']); ?>
                                                        </span>
                                                        <button
                                                            class="mt-3 text-xs text-primary hover:text-blue-700 font-medium flex items-center gap-1 ml-auto">
                                                            <i class="fas fa-info-circle"></i>
                                                            Details
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <div
                                            class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                                            <i class="fas fa-calendar-xmark text-slate-400 text-2xl"></i>
                                        </div>
                                        <p class="text-slate-500 mb-2">No upcoming appointments</p>
                                        <a href="book_appointment.php"
                                            class="text-primary hover:text-blue-700 font-medium text-sm inline-flex items-center gap-1">
                                            <span>Book your first appointment</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Diagnostics & Medications -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Diagnostics -->
                            <div class="glass-card rounded-2xl border border-slate-200/50 overflow-hidden">
                                <div class="p-6 border-b border-slate-200/50">
                                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-stethoscope text-primary"></i>
                                        Active Diagnostics
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <?php if (count($diagnostics) > 0): ?>
                                        <div class="space-y-4">
                                            <?php foreach ($diagnostics as $diag): ?>
                                                <div
                                                    class="p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200/50">
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex items-start gap-3">
                                                            <div
                                                                class="p-2 rounded-lg bg-gradient-to-br from-orange-50 to-orange-100 text-orange-600">
                                                                <i class="fas fa-heartbeat"></i>
                                                            </div>
                                                            <div>
                                                                <h4 class="font-bold text-slate-900">
                                                                    <?php echo htmlspecialchars($diag['condition_name']); ?>
                                                                </h4>
                                                                <p class="text-xs text-slate-500 mt-1">
                                                                    <i class="fas fa-user-md mr-1"></i>
                                                                    <?php echo htmlspecialchars($diag['doctor_name']); ?>
                                                                </p>
                                                                <p class="text-xs text-slate-500">
                                                                    <i class="far fa-calendar mr-1"></i>
                                                                    Since <?php echo htmlspecialchars($diag['since_date']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-1 rounded-full">
                                                            Active
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-6 text-slate-500">
                                            <i class="fas fa-check-circle text-3xl text-green-400 mb-3"></i>
                                            <p class="text-sm">No active diagnostics</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Medications -->
                            <div class="glass-card rounded-2xl border border-slate-200/50 overflow-hidden">
                                <div class="p-6 border-b border-slate-200/50">
                                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-pills text-primary"></i>
                                        Current Medications
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <?php if (count($medications) > 0): ?>
                                        <div class="space-y-4">
                                            <?php foreach ($medications as $med):
                                                $percent = ($med['capsules_left'] / $med['total_capsules']) * 100;
                                                $color = $med['color_class'] == 'orange' ? 'orange' : 'blue';
                                                ?>
                                                <div
                                                    class="p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200/50">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="p-2 rounded-lg bg-gradient-to-br from-<?php echo $color; ?>-50 to-<?php echo $color; ?>-100 text-<?php echo $color; ?>-600">
                                                                <i class="fas fa-capsules"></i>
                                                            </div>
                                                            <div>
                                                                <h4 class="font-bold text-slate-900">
                                                                    <?php echo htmlspecialchars($med['name']); ?>
                                                                </h4>
                                                                <p class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($med['dosage']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="text-xs font-bold bg-slate-100 text-slate-700 px-2 py-1 rounded-full">
                                                            <?php echo htmlspecialchars($med['frequency']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                                                        <span><?php echo $med['capsules_left']; ?> of
                                                            <?php echo $med['total_capsules']; ?> remaining</span>
                                                        <span
                                                            class="font-semibold text-slate-700"><?php echo round($percent); ?>%</span>
                                                    </div>
                                                    <div class="medication-progress">
                                                        <div class="medication-progress-bar bg-gradient-to-r from-<?php echo $color; ?>-400 to-<?php echo $color; ?>-600"
                                                            style="width: <?php echo $percent; ?>%"></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-6 text-slate-500">
                                            <i class="fas fa-prescription-bottle text-3xl text-slate-300 mb-3"></i>
                                            <p class="text-sm">No active medications</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="glass-card rounded-2xl border border-slate-200/50 p-6">
                            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-bolt text-yellow-500"></i>
                                Quick Actions
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="request_blood.php"
                                    class="p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-xl border border-red-200 text-center hover:border-red-300 transition-all duration-300 group">
                                    <div
                                        class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <i class="fas fa-hand-holding-medical"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">Request Blood</p>
                                </a>


                                <a href="reports.php"
                                    class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 text-center hover:border-green-300 transition-all duration-300 group">
                                    <div
                                        class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <i class="fas fa-file-medical"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">View Reports</p>
                                </a>

                                <a href="profile.php"
                                    class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 text-center hover:border-purple-300 transition-all duration-300 group">
                                    <div
                                        class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <i class="fas fa-user-edit"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">Update Profile</p>
                                </a>

                                <a href="camps.php"
                                    class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border border-orange-200 text-center hover:border-orange-300 transition-all duration-300 group">
                                    <div
                                        class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">Find Camps</p>
                                </a>

                                <a href="ai_summary.php"
                                    class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-200 text-center hover:border-purple-300 transition-all duration-300 group col-span-2">
                                    <div
                                        class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined">auto_awesome</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">AI Health Summarizer</p>
                                    <p class="text-xs text-slate-500 mt-1">Analyze reports & symptoms</p>
                                </a>
                            </div>
                        </div>

                        <!-- Recent Reports -->
                        <div class="glass-card rounded-2xl border border-slate-200/50 overflow-hidden">
                            <div class="p-6 border-b border-slate-200/50">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-file-alt text-primary"></i>
                                        Recent Reports
                                    </h3>
                                    <a href="reports.php"
                                        class="text-xs text-primary hover:text-blue-700 font-semibold">
                                        View All
                                    </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <?php if (count($reports) > 0): ?>
                                    <div class="space-y-4">
                                        <?php foreach ($reports as $report): ?>
                                            <div
                                                class="p-3 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200/50 hover:border-primary/50 transition-colors cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="p-2 rounded-lg bg-gradient-to-br from-teal-50 to-teal-100 text-teal-600">
                                                            <i class="fas fa-flask"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-sm font-semibold text-slate-900">
                                                                <?php echo htmlspecialchars($report['test_name']); ?>
                                                            </h4>
                                                            <p class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($report['test_date']); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-bold text-slate-900">
                                                            <?php echo htmlspecialchars($report['result_value']); ?>
                                                        </p>
                                                        <p class="text-xs text-slate-500">Result</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 text-slate-500">
                                        <i class="fas fa-file-medical-alt text-2xl text-slate-300 mb-2"></i>
                                        <p class="text-sm">No recent reports</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- My Blood Requests Status -->
                        <div class="glass-card rounded-2xl border border-slate-200/50 overflow-hidden">
                            <div class="p-6 border-b border-slate-200/50">
                                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fas fa-heartbeat text-red-500"></i>
                                    My Blood Requests
                                </h3>
                                <p class="text-sm text-slate-500 mt-1">Status of your requests</p>
                            </div>
                            <div class="p-6">
                                <?php if (count($blood_requests) > 0): ?>
                                    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                                        <?php foreach ($blood_requests as $request):
                                            $status_normalized = strtolower(trim($request['status']));
                                            $status_class = match ($status_normalized) {
                                                'approved' => 'bg-green-100 text-green-700 border-green-200',
                                                'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                                'fulfilled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                default => 'bg-yellow-50 text-yellow-700 border-yellow-200'
                                            };
                                            $icon = match ($status_normalized) {
                                                'approved' => 'check_circle',
                                                'rejected' => 'cancel',
                                                'fulfilled' => 'volunteer_activism',
                                                default => 'hourglass_empty'
                                            };
                                            ?>
                                            <div class="p-4 rounded-xl border <?php echo $status_class; ?> bg-opacity-50">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div>
                                                        <h4 class="font-bold">
                                                            <?php echo htmlspecialchars($request['patient_name']); ?>
                                                        </h4>
                                                        <p class="text-xs opacity-75">
                                                            <?php echo htmlspecialchars($request['blood_group']); ?> •
                                                            <?php echo $request['units_needed']; ?> Units
                                                        </p>
                                                    </div>
                                                    <span class="material-symbols-outlined text-lg">
                                                        <?php echo $icon; ?>
                                                    </span>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between text-xs font-medium opacity-90 mt-2">
                                                    <span><?php echo $status_normalized === 'approved' ? 'Accepted' : ucfirst($status_normalized); ?></span>
                                                    <span><?php echo date('M d, Y', strtotime($request['created_at'])); ?></span>
                                                </div>

                                                <?php if ($status_normalized === 'approved'): ?>
                                                    <div class="mt-3 pt-3 border-t border-green-200/50">
                                                        <a href="view_donors.php?request_id=<?php echo $request['id']; ?>"
                                                            class="block w-full text-center py-2 bg-white/50 hover:bg-white/80 rounded-lg text-xs font-bold text-green-800 transition-colors border border-green-200/50">
                                                            <i class="fas fa-users mr-1"></i> View Matching Donors
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <div
                                            class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-50 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-red-300 text-3xl">bloodtype</span>
                                        </div>
                                        <p class="text-slate-500 mb-4">No blood requests made yet</p>
                                        <a href="request_blood.php"
                                            class="inline-block px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                                            Request Blood
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Floating Action Button -->
    <a href="book_appointment.php" class="fixed bottom-8 right-8 z-30 floating-action">
        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-r from-primary to-blue-600 rounded-full blur-md opacity-70">
            </div>
            <div
                class="relative w-16 h-16 rounded-full gradient-primary flex items-center justify-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110">
                <i class="fas fa-calendar-plus text-white text-xl"></i>
            </div>
        </div>
    </a>

    <script>
        // Initialize progress rings
        document.addEventListener('DOMContentLoaded', function () {
            const rings = document.querySelectorAll('.progress-ring');
            rings.forEach(ring => {
                const circle = ring.querySelector('circle');
                const radius = circle.r.baseVal.value;
                const circumference = radius * 2 * Math.PI;

                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = circumference;

                const offset = circumference - (85 / 100 * circumference);
                setTimeout(() => {
                    circle.style.strokeDashoffset = offset;
                }, 100);
            });

            // Toggle mobile sidebar
            const menuBtn = document.querySelector('button.lg\\:hidden');
            const sidebar = document.querySelector('aside');

            if (menuBtn) {
                menuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('hidden');
                    sidebar.classList.toggle('flex');
                    sidebar.classList.add('absolute', 'z-40', 'inset-y-0', 'left-0');
                });
            }

            // Reminder checkboxes
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const reminderItem = this.closest('.reminder-item');
                    if (this.checked) {
                        reminderItem.classList.add('completed');
                        // Simulate API call
                        setTimeout(() => {
                            this.disabled = false;
                        }, 1000);
                    }
                });
            });
        });
    </script>
    <?php include('review_modal_partial.php'); ?>
    <script src="js/notifications.js"></script>
</body>

</html>