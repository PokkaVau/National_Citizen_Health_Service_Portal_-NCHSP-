<?php
require('config/db.php');
require('auth_session.php');

$message = "";
$user_id = $_SESSION['user_id'];
$selected_doctor_id = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : null;

// Handle Booking Submission
if (isset($_POST['book_slot'])) {
    $schedule_id = $_POST['schedule_id'];
    $doctor_id = $_POST['doctor_id'];
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    try {
        // 1. Check if eligible (not already booked)
        $check = $pdo->prepare("SELECT * FROM doctor_schedules WHERE id = ? AND is_booked = 0");
        $check->execute([$schedule_id]);
        $slot = $check->fetch();

        if ($slot) {
            // 2. Book it
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, schedule_id, appointment_date, description, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            // appointment_date is datetime, schedule has date and start_time
            $appt_datetime = $slot['available_date'] . ' ' . $slot['start_time'];
            $stmt->execute([$user_id, $doctor_id, $schedule_id, $appt_datetime, $description]);

            $update = $pdo->prepare("UPDATE doctor_schedules SET is_booked = 1 WHERE id = ?");
            $update->execute([$schedule_id]);

            $pdo->commit();

            // Notify Doctor
            require_once 'config/notifications.php';
            // Get Doctor's Admin ID
            $docStmt = $pdo->prepare("SELECT admin_id, name FROM doctors WHERE id = ?");
            $docStmt->execute([$doctor_id]);
            $docInfo = $docStmt->fetch();

            if ($docInfo && $docInfo['admin_id']) {
                $userName = $_SESSION['user_name'] ?? 'A patient'; // Ensure user_name is available or fetch it
                createNotification($pdo, $docInfo['admin_id'], 'admin', "New appointment booked by patient for " . date('M d, h:i A', strtotime($appt_datetime)), 'info', '/dbms/doctor/dashboard.php');
            }

            $message = "🎉 Appointment booked successfully! You'll receive a confirmation email shortly.";
            // Redirect to appointments page
            header("Location: my_appointments.php?msg=" . urlencode($message));
            exit();
        } else {
            $message = "⚠️ This time slot was just taken. Please select another slot.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "❌ Error booking appointment: " . $e->getMessage();
    }
}

// Fetch Doctors
try {
    $stmt = $pdo->query("
        SELECT d.*, 
               (SELECT AVG(rating) FROM doctor_reviews dr WHERE dr.doctor_id = d.id) as avg_rating,
               (SELECT COUNT(*) FROM doctor_reviews dr WHERE dr.doctor_id = d.id) as review_count
        FROM doctors d 
        ORDER BY name ASC
    ");
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    $doctors = [];
}

// Fetch Slots if Doctor Selected
$slots = [];
$selected_doctor = null;
if ($selected_doctor_id) {
    try {
        // Get Doctor Name
        $stmt = $pdo->prepare("
            SELECT d.*, 
               (SELECT AVG(rating) FROM doctor_reviews dr WHERE dr.doctor_id = d.id) as avg_rating,
               (SELECT COUNT(*) FROM doctor_reviews dr WHERE dr.doctor_id = d.id) as review_count
            FROM doctors d WHERE id = ?
        ");
        $stmt->execute([$selected_doctor_id]);
        $selected_doctor = $stmt->fetch();

        // Get Available Slots
        $stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = ? AND is_booked = 0 AND available_date >= CURDATE() ORDER BY available_date ASC, start_time ASC");
        $stmt->execute([$selected_doctor_id]);
        $slots = $stmt->fetchAll();
    } catch (PDOException $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        .doctor-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
        }

        .doctor-card:hover {
            transform: translateX(4px);
            border-left-color: var(--primary);
            background: linear-gradient(to right, #f0f9ff, #ffffff);
        }

        .doctor-card.selected {
            border-left-color: var(--primary);
            background: linear-gradient(to right, #dbeafe, #eff6ff);
            box-shadow: inset 4px 0 0 var(--primary);
        }

        .slot-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .slot-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1);
        }

        .slot-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), #8b5cf6);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .slot-card:hover::before {
            transform: scaleX(1);
        }

        .availability-badge {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .success-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .error-gradient {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .shadow-soft {
            box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
        }

        .glow {
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.3);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bounce-in {
            animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <!-- Enhanced Navbar -->
    <nav class="gradient-bg px-6 py-4 shadow-soft">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-2xl">local_hospital</span>
                    </div>
                    <div>
                        <h1 class="text-white font-bold text-xl">NCHSP Portal</h1>
                        <p class="text-white/80 text-sm">Book Medical Appointments</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <a href="dashboard.php"
                    class="text-white/90 hover:text-white font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <a href="my_appointments.php"
                    class="text-white/90 hover:text-white font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">event</span>
                    My Appointments
                </a>
                <a href="logout.php"
                    class="text-white/90 hover:text-white font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">logout</span>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between mb-8">
            <a href="dashboard.php"
                class="flex items-center gap-3 px-5 py-3 bg-white rounded-xl shadow-soft hover:shadow-lg transition-all duration-300 group">
                <span
                    class="material-symbols-outlined text-blue-600 group-hover:-translate-x-1 transition-transform">arrow_back</span>
                <span class="font-medium text-gray-700">Back to Dashboard</span>
            </a>

            <div class="text-right">
                <h1 class="text-3xl font-bold text-gray-900">Book Appointment</h1>
                <p class="text-gray-600">Schedule your medical consultation with ease</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="fade-in mb-8">
                <div
                    class="<?php echo strpos($message, 'Error') !== false || strpos($message, '❌') !== false || strpos($message, '⚠️') !== false ? 'error-gradient' : 'success-gradient'; ?> text-white p-5 rounded-2xl shadow-lg flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <?php if (strpos($message, '❌') !== false): ?>
                            <span class="material-symbols-outlined text-2xl">error</span>
                        <?php elseif (strpos($message, '⚠️') !== false): ?>
                            <span class="material-symbols-outlined text-2xl">warning</span>
                        <?php else: ?>
                            <span class="material-symbols-outlined text-2xl">check_circle</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-lg"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                    <button onclick="this.parentElement.remove()"
                        class="p-2 hover:bg-white/20 rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Doctor Selection Panel -->
            <div class="lg:col-span-1">
                <div class="glass-card rounded-2xl p-6 shadow-soft sticky top-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Select Doctor</h2>
                            <p class="text-sm text-gray-500">Choose from our expert medical team</p>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1 bg-blue-50 rounded-lg">
                            <span class="material-symbols-outlined text-blue-600 text-sm">group</span>
                            <span class="text-sm font-medium text-blue-600"><?php echo count($doctors); ?>
                                Doctors</span>
                        </div>
                    </div>

                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                        <?php foreach ($doctors as $doc): ?>
                            <a href="?doctor_id=<?php echo $doc['id']; ?>"
                                class="block doctor-card <?php echo $selected_doctor_id == $doc['id'] ? 'selected' : ''; ?> p-4 rounded-xl border border-gray-100">
                                <div class="flex items-start gap-4">
                                    <div class="relative">
                                        <div
                                            class="size-14 rounded-xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center overflow-hidden">
                                            <?php if (!empty($doc['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars(str_replace('../', '', $doc['profile_picture'])); ?>"
                                                    class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <span class="material-symbols-outlined text-2xl text-blue-600">person</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($selected_doctor_id == $doc['id']): ?>
                                            <div
                                                class="absolute -top-1 -right-1 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                                <span class="material-symbols-outlined text-white text-xs">check</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h3 class="font-bold text-gray-900">
                                                <?php echo htmlspecialchars($doc['name']); ?>
                                            </h3>
                                            <span
                                                class="material-symbols-outlined text-blue-600 text-sm">arrow_forward</span>
                                        </div>
                                        <p class="text-blue-600 font-medium text-sm mb-1">
                                            <?php echo htmlspecialchars($doc['specialization']); ?>
                                        </p>
                                        <?php if (!empty($doc['experience'])): ?>
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <span class="material-symbols-outlined text-xs">work</span>
                                                <span><?php echo htmlspecialchars($doc['experience']); ?> experience</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Help Text -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-blue-600">info</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900 mb-1">Need help choosing?</p>
                                <p class="text-xs text-gray-600">Select a doctor to view their availability and
                                    specialties.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slots & Booking Panel -->
            <div class="lg:col-span-2">
                <?php if ($selected_doctor): ?>
                    <div class="fade-in">
                        <!-- Doctor Profile Header -->
                        <div class="glass-card rounded-2xl p-8 shadow-soft mb-8">
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                                <div class="relative">
                                    <div
                                        class="size-24 rounded-2xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center overflow-hidden border-4 border-white shadow-lg">
                                        <?php if (!empty($selected_doctor['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars(str_replace('../', '', $selected_doctor['profile_picture'])); ?>"
                                                class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="material-symbols-outlined text-4xl text-blue-600">person</span>
                                        <?php endif; ?>
                                    </div>
                                    <div
                                        class="absolute -bottom-2 -right-2 w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                        <span class="material-symbols-outlined text-white">medical_services</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                                        <div>
                                            <h1 class="text-3xl font-bold text-gray-900">
                                                <?php echo htmlspecialchars($selected_doctor['name']); ?>
                                            </h1>
                                            <div class="flex items-center gap-3 mt-2">
                                                <span
                                                    class="px-4 py-1.5 bg-blue-100 text-blue-700 rounded-full font-semibold text-sm">
                                                    <?php echo htmlspecialchars($selected_doctor['specialization']); ?>
                                                </span>
                                                <span class="flex items-center gap-1 text-sm text-gray-600">
                                                    <span class="material-symbols-outlined text-sm">star</span>
                                                    <?php echo $selected_doctor['avg_rating'] ? number_format($selected_doctor['avg_rating'], 1) : 'New'; ?>
                                                    (<?php echo $selected_doctor['review_count']; ?>)
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 px-4 py-2 bg-green-50 rounded-lg">
                                            <span class="material-symbols-outlined text-green-600">check_circle</span>
                                            <span class="text-sm font-medium text-green-700">Available for Booking</span>
                                        </div>
                                    </div>
                                    <?php if (!empty($selected_doctor['bio'])): ?>
                                        <p class="text-gray-600 leading-relaxed">
                                            <?php echo nl2br(htmlspecialchars($selected_doctor['bio'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Available Slots Section -->
                        <div class="glass-card rounded-2xl p-8 shadow-soft">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-gradient-to-r from-blue-100 to-purple-100 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-blue-600">calendar_month</span>
                                        </div>
                                        Available Time Slots
                                    </h2>
                                    <p class="text-gray-600 mt-2">Select a convenient time for your consultation</p>
                                </div>
                                <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-xl">
                                    <span class="material-symbols-outlined text-blue-600">schedule</span>
                                    <span class="font-bold text-blue-600"><?php echo count($slots); ?> slots
                                        available</span>
                                </div>
                            </div>

                            <?php if (count($slots) > 0): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <?php foreach ($slots as $slot):
                                        $slot_date = date('M d, Y', strtotime($slot['available_date']));
                                        $day_name = date('D', strtotime($slot['available_date']));
                                        $start_time = date('h:i A', strtotime($slot['start_time']));
                                        $end_time = date('h:i A', strtotime($slot['end_time']));
                                        ?>
                                        <div
                                            class="slot-card bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-xl">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <div class="flex items-center gap-3 mb-2">
                                                        <div
                                                            class="w-10 h-10 rounded-lg bg-gradient-to-r from-green-100 to-emerald-100 flex items-center justify-center">
                                                            <span
                                                                class="material-symbols-outlined text-green-600">event_available</span>
                                                        </div>
                                                        <div>
                                                            <span
                                                                class="font-bold text-gray-900 text-lg"><?php echo $slot_date; ?></span>
                                                            <span
                                                                class="text-sm text-gray-500 ml-2">(<?php echo $day_name; ?>)</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-4 mb-2">
                                                        <span class="text-gray-600 font-medium flex items-center gap-2">
                                                            <span class="material-symbols-outlined text-sm">schedule</span>
                                                            <?php echo $start_time; ?> - <?php echo $end_time; ?>
                                                        </span>
                                                    </div>
                                                    <?php if (!empty($slot['location'])): ?>
                                                        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                                                            <span
                                                                class="material-symbols-outlined text-sm text-red-500">location_on</span>
                                                            <span><?php echo htmlspecialchars($slot['location']); ?></span>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="flex items-center gap-4">
                                                        <span
                                                            class="availability-badge text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold flex items-center gap-1">
                                                            <span
                                                                class="availability-badge text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold flex items-center gap-1">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                                Available
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Booking Form -->
                                            <form action="" method="post" class="mt-6">
                                                <input type="hidden" name="schedule_id" value="<?php echo $slot['id']; ?>">
                                                <input type="hidden" name="doctor_id" value="<?php echo $selected_doctor['id']; ?>">

                                                <div class="mb-4">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                                        <span
                                                            class="material-symbols-outlined text-gray-500 text-sm">description</span>
                                                        Reason for Visit
                                                    </label>
                                                    <input type="text" name="description"
                                                        placeholder="Brief description of symptoms or consultation needed..."
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                                        required>
                                                    <p class="text-xs text-gray-500 mt-2">Please describe your symptoms or reason
                                                        for consultation</p>
                                                </div>

                                                <button type="submit" name="book_slot"
                                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-3 group">
                                                    <span
                                                        class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">book_online</span>
                                                    <span>Book This Slot</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-16">
                                    <div
                                        class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-r from-gray-100 to-gray-200 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-gray-400 text-6xl">event_busy</span>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No Available Slots</h3>
                                    <p class="text-gray-600 max-w-md mx-auto mb-8">
                                        This doctor is currently fully booked. Please check back later or select another doctor.
                                    </p>
                                    <a href="?doctor_id="
                                        class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                                        <span class="material-symbols-outlined">arrow_back</span>
                                        Choose Another Doctor
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div
                        class="h-full flex flex-col items-center justify-center text-center p-12 bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-soft border-2 border-dashed border-gray-200">
                        <div class="w-40 h-40 mb-8 relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-blue-200 to-purple-200 rounded-full animate-pulse">
                            </div>
                            <div
                                class="absolute inset-4 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600 text-6xl">medical_services</span>
                            </div>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">Select a Doctor</h3>
                        <p class="text-gray-600 text-lg mb-10 max-w-md">
                            Choose a healthcare professional from our expert medical team to view their availability and
                            book your appointment.
                        </p>
                        <div class="flex items-center gap-2 text-gray-500">
                            <span class="material-symbols-outlined animate-bounce">arrow_back</span>
                            <span class="font-medium">← Click on a doctor's card to begin</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600">verified</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Secure Booking</h4>
                        <p class="text-sm text-gray-600">Encrypted Data</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-600">support_agent</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">24/7 Support</h4>
                        <p class="text-sm text-gray-600">Need help? Contact support</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-purple-600">event_available</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Easy Rescheduling</h4>
                        <p class="text-sm text-gray-600">Change appointments anytime</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function () {
            // Add bounce animation to selected doctor card
            const selectedCard = document.querySelector('.doctor-card.selected');
            if (selectedCard) {
                selectedCard.classList.add('bounce-in');
            }

            // Add animation to slot cards
            const slotCards = document.querySelectorAll('.slot-card');
            slotCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });

            // Auto-dismiss success messages after 5 seconds
            const successMessages = document.querySelectorAll('[class*="success-gradient"]');
            successMessages.forEach(msg => {
                setTimeout(() => {
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(-10px)';
                    setTimeout(() => msg.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>

</html>