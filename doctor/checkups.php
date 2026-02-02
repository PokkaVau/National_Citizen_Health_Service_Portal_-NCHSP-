<?php
require_once('../config/db.php');
require_once('../auth_session.php');
check_doctor_login();

$admin_id = $_SESSION['admin_id'];

// Get Doctor Info
if ($_SESSION['admin_role'] == 'assistant') {
    $stmt = $pdo->prepare("SELECT d.* FROM doctors d JOIN assistants a ON d.id = a.doctor_id WHERE a.admin_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE admin_id = ?");
}
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

// Date Selection
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch Appointments for Date
$stmt = $pdo->prepare("
    SELECT a.*, u.name as user_name, u.mobile, u.blood_type, u.dob
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.doctor_id = ? AND DATE(a.appointment_date) = ?
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$doctor_id, $selected_date]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Checkups - Doctor Portal</title>
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
                    <h1 class="text-2xl font-bold text-slate-800">Daily Checkups</h1>
                    <p class="text-slate-500">Manage patient visits for specific dates</p>
                </div>
            </header>

            <!-- Date Filter -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8">
                <form action="" method="get" class="flex items-end gap-4">
                    <div class="flex-1 max-w-xs">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Select Date</label>
                        <input type="date" name="date" value="<?php echo $selected_date; ?>"
                            onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                    <div class="pb-2 text-slate-500 text-sm">
                        Showing
                        <?php echo count($appointments); ?> patients for
                        <?php echo date('M d, Y', strtotime($selected_date)); ?>
                    </div>
                </form>
            </div>

            <!-- Patient List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (count($appointments) > 0): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="size-16 rounded-full bg-indigo-50 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <span class="material-symbols-outlined text-3xl text-indigo-400">person</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 line-clamp-1"><?php echo htmlspecialchars($appt['user_name']); ?></h3>
                                    <div class="text-xs text-slate-500 flex items-center gap-2 mt-1">
                                        <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-600"><?php echo date('h:i A', strtotime($appt['appointment_date'])); ?></span>
                                        <span class="<?php echo $appt['status'] == 'completed' ? 'text-green-600' : 'text-amber-600'; ?> font-medium">
                                            <?php echo ucfirst($appt['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <span class="material-symbols-outlined text-lg text-slate-400">call</span>
                                    <?php echo htmlspecialchars($appt['mobile']); ?>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <span class="material-symbols-outlined text-lg text-slate-400">bloodtype</span>
                                    <?php echo htmlspecialchars($appt['blood_type'] ?? 'N/A'); ?>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <span class="material-symbols-outlined text-lg text-slate-400">cake</span>
                                    <?php 
                                        $age = date_diff(date_create($appt['dob']), date_create('today'))->y;
                                        echo $age . " Years"; 
                                    ?>
                                </div>
                            </div>

                            <a href="patient_care.php?user_id=<?php echo $appt['user_id']; ?>&date=<?php echo $selected_date; ?>&appt_id=<?php echo $appt['id']; ?>"
                                class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-lg">medical_services</span>
                                Manage Care
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12">
                        <div class="size-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-4xl text-slate-300">event_busy</span>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900">No appointments found</h3>
                        <p class="text-slate-500">No patients scheduled for this date.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>