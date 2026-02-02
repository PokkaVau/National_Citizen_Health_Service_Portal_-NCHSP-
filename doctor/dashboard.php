<?php
require('../config/db.php');
require('../auth_session.php');
check_doctor_login();

$admin_id = $_SESSION['admin_id'];

// 1. Get Doctor Info based on Role
if ($_SESSION['admin_role'] == 'assistant') {
    // If assistant, get the doctor they work for
    $stmt = $pdo->prepare("SELECT d.* FROM doctors d JOIN assistants a ON d.id = a.doctor_id WHERE a.admin_id = ?");
} else {
    // If doctor, get their own info
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE admin_id = ?");
}
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    die("Doctor profile not found.");
}

$doctor_id = $doctor['id'];

// Handle Status Updates
if (isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['update_status'];

    // Security check: ensure appointment belongs to this doctor
    $check = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $check->execute([$appointment_id, $doctor_id]);

    if ($check->fetch()) {
        $update = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $update->execute([$new_status, $appointment_id]);
        $message = "Appointment updated to " . $new_status;

        // Notify User
        require_once '../config/notifications.php';
        // Get User ID from Appointment (We need to fetch it first or join in the check query)
        $userCheck = $pdo->prepare("SELECT user_id FROM appointments WHERE id = ?");
        $userCheck->execute([$appointment_id]);
        $apptUser = $userCheck->fetch();

        if ($apptUser) {
            $msgType = $new_status == 'confirmed' || $new_status == 'completed' ? 'success' : 'warning';
            createNotification($pdo, $apptUser['user_id'], 'user', "Your appointment with Dr. " . $doctor['name'] . " has been " . $new_status, $msgType, '/dbms/my_appointments.php');
        }
    }
}

// Fetch Appointments
$stmt = $pdo->prepare("
    SELECT a.*, u.name as user_name, u.mobile, u.blood_type 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.doctor_id = ? 
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - NCHSP</title>
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
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-slate-500">Welcome back, <?php echo htmlspecialchars($doctor['name']); ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Notification Bell for Doctor -->
                    <div class="relative">
                        <button id="notificationBtn"
                            class="p-2 rounded-full bg-white shadow-sm hover:shadow-md transition-shadow text-slate-600 hover:text-indigo-600">
                            <span class="material-symbols-outlined">notifications</span>
                        </button>
                        <span
                            class="notification-dot hidden absolute top-0 right-0 size-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    </div>

                    <div class="hidden md:block">
                        <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-sm font-medium">
                            <?php echo date('l, F j, Y'); ?>
                        </span>
                    </div>
                </div>
            </header>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="p-4 bg-blue-50 text-blue-600 rounded-xl">
                            <span class="material-symbols-outlined text-2xl">calendar_month</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Appointments</p>
                            <p class="text-2xl font-bold text-slate-800">
                                <?php echo count($appointments); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="p-4 bg-amber-50 text-amber-600 rounded-xl">
                            <span class="material-symbols-outlined text-2xl">pending_actions</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Pending</p>
                            <p class="text-2xl font-bold text-slate-800">
                                <?php echo count(array_filter($appointments, fn($a) => $a['status'] == 'pending')); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="p-4 bg-green-50 text-green-600 rounded-xl">
                            <span class="material-symbols-outlined text-2xl">check_circle</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Completed</p>
                            <p class="text-2xl font-bold text-slate-800">
                                <?php echo count(array_filter($appointments, fn($a) => $a['status'] == 'completed')); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-600">event_note</span>
                        Appointment Schedule
                    </h2>
                </div>

                <?php if (count($appointments) == 0): ?>
                    <div class="p-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-6xl text-slate-200 mb-4">event_busy</span>
                        <p class="text-lg">No appointments scheduled.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-gray-50 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Date & Time</th>
                                    <th class="px-6 py-4">Patient Name</th>
                                    <th class="px-6 py-4">Contact</th>
                                    <th class="px-6 py-4">Reason</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($appointments as $appt): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-slate-900">
                                                <?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?>
                                            </div>
                                            <div class="text-xs text-indigo-500">
                                                <?php echo date('h:i A', strtotime($appt['appointment_date'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">
                                                <?php echo htmlspecialchars($appt['user_name']); ?>
                                            </div>
                                            <div class="text-xs text-slate-500">Blood:
                                                <?php echo htmlspecialchars($appt['blood_type']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo htmlspecialchars($appt['mobile']); ?>
                                        </td>
                                        <td class="px-6 py-4 max-w-xs truncate"
                                            title="<?php echo htmlspecialchars($appt['description']); ?>">
                                            <?php echo htmlspecialchars($appt['description']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $status_colors = [
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'confirmed' => 'bg-blue-100 text-blue-700',
                                                'completed' => 'bg-green-100 text-green-700',
                                                'cancelled' => 'bg-red-100 text-red-700'
                                            ];
                                            $color = $status_colors[$appt['status']] ?? 'bg-gray-100';
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color; ?>">
                                                <?php echo ucfirst($appt['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php if ($appt['status'] != 'completed' && $appt['status'] != 'cancelled'): ?>
                                                <form action="" method="post" class="flex justify-center gap-2">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                                    <button type="submit" name="update_status" value="completed"
                                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                                        title="Mark Complete">
                                                        <span class="material-symbols-outlined">check_circle</span>
                                                    </button>
                                                    <button type="submit" name="update_status" value="cancelled"
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Cancel">
                                                        <span class="material-symbols-outlined">cancel</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-slate-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <script src="../js/notifications.js"></script>
</body>

</html>