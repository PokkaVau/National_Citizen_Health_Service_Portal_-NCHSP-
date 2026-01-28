<?php
require('../config/db.php');
require('../auth_session.php');
check_doctor_login();

$admin_id = $_SESSION['admin_id'];
$message = "";
$error = "";

// Get Doctor ID based on Role
if ($_SESSION['admin_role'] == 'assistant') {
    $stmt = $pdo->prepare("SELECT d.id FROM doctors d JOIN assistants a ON d.id = a.doctor_id WHERE a.admin_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE admin_id = ?");
}
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

// Handle Add Slot
if (isset($_POST['add_slot'])) {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $duration = (int) $_POST['duration'];

    // Basic validation
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = "Cannot add slots in the past!";
    } elseif (strtotime($start_time) >= strtotime($end_time)) {
        $error = "Start time must be before end time!";
    } else {
        // Generate Slots Loop
        $current_start = strtotime($date . ' ' . $start_time);
        $final_end = strtotime($date . ' ' . $end_time);
        $slots_created = 0;

        $stmt = $pdo->prepare("INSERT INTO doctor_schedules (doctor_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");

        while ($current_start < $final_end) {
            $current_end = $current_start + ($duration * 60);

            // Stop if next slot exceeds final end time
            if ($current_end > $final_end) {
                break;
            }

            $slot_start_str = date('H:i:s', $current_start);
            $slot_end_str = date('H:i:s', $current_end);

            if ($stmt->execute([$doctor_id, $date, $slot_start_str, $slot_end_str])) {
                $slots_created++;
            }

            // Move to next slot
            $current_start = $current_end;
        }

        if ($slots_created > 0) {
            $message = "Successfully created $slots_created time slots!";
        } else {
            $error = "Failed to create slots. Please check your time range.";
        }
    }
}

// Handle Delete Slot
if (isset($_POST['delete_slot'])) {
    $slot_id = $_POST['slot_id'];
    $stmt = $pdo->prepare("DELETE FROM doctor_schedules WHERE id = ? AND doctor_id = ? AND is_booked = 0");
    if ($stmt->execute([$slot_id, $doctor_id])) {
        $message = "Slot deleted successfully!";
    } else {
        $error = "Failed to delete slot (it might be booked).";
    }
}

// Fetch Schedules
$stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = ? ORDER BY available_date ASC, start_time ASC");
$stmt->execute([$doctor_id]);
$schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - Doctor Portal</title>
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
                <h1 class="text-2xl font-bold text-slate-800">Manage Schedule</h1>
            </header>

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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Add Slot Form -->
                <div class="md:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100 sticky top-4">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">add_circle</span> Add Availability
                        </h2>
                        <form action="" method="post" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                                <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Start Time</label>
                                    <input type="time" name="start_time" required
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">End Time</label>
                                    <input type="time" name="end_time" required
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Slot Duration</label>
                                <select name="duration"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="15">15 Minutes</option>
                                    <option value="20" selected>20 Minutes</option>
                                    <option value="30">30 Minutes</option>
                                    <option value="45">45 Minutes</option>
                                    <option value="60">1 Hour</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">System will auto-generate slots</p>
                            </div>

                            <button type="submit" name="add_slot"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg transition-colors">
                                Generate Slots
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Slots List -->
                <div class="md:col-span-2 space-y-4">
                    <h2 class="text-lg font-bold mb-2">My Availability</h2>
                    <?php if (count($schedules) > 0): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-indigo-50/50 text-xs uppercase font-semibold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3">Time</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($schedules as $slot): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 font-medium text-slate-900">
                                                <?php echo date('M d, Y', strtotime($slot['available_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600">
                                                <?php echo date('h:i A', strtotime($slot['start_time'])) . ' - ' . date('h:i A', strtotime($slot['end_time'])); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if ($slot['is_booked']): ?>
                                                    <span
                                                        class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold">Booked</span>
                                                <?php else: ?>
                                                    <span
                                                        class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold">Available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <?php if (!$slot['is_booked']): ?>
                                                    <form action="" method="post" onsubmit="return confirm('Delete this slot?');">
                                                        <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                                                        <button type="submit" name="delete_slot"
                                                            class="text-red-500 hover:text-red-700">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-gray-300 material-symbols-outlined">lock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div
                            class="bg-white p-8 rounded-2xl border border-dashed border-gray-300 text-center text-slate-500">
                            <p>No availability slots added yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>