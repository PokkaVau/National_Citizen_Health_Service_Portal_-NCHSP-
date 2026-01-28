<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];

// Fetch all appointments for the user
try {
    $stmt = $pdo->prepare("
        SELECT a.*, d.name as doctor_name, d.specialization 
        FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.user_id = ? 
        ORDER BY a.appointment_date DESC
    ");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $appointments = [];
    $error = "Error fetching appointments: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - NCHSP</title>
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
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600 text-3xl">local_hospital</span>
                <span class="text-xl font-bold text-gray-800">NCHSP</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 font-medium text-sm">Dashboard</a>
                <a href="doctors_list.php" class="text-gray-600 hover:text-blue-600 font-medium text-sm">Doctors</a>
                <a href="logout.php" class="text-red-500 hover:text-red-700 font-medium text-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-indigo-100 text-indigo-600 rounded-full">
                    <span class="material-symbols-outlined">event_note</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Appointments</h1>
                    <p class="text-slate-500 text-sm">History of your medical visits</p>
                </div>
            </div>
            <a href="book_appointment.php"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm transition-colors shadow-sm">
                <span class="material-symbols-outlined">add</span>
                Book New
            </a>
        </div>

        <?php if (count($appointments) == 0): ?>
            <div class="bg-white p-12 rounded-xl shadow-sm border border-gray-200 text-center">
                <span class="material-symbols-outlined text-6xl text-gray-200 mb-4">event_busy</span>
                <p class="text-gray-500 text-lg">You haven't booked any appointments yet.</p>
                <a href="book_appointment.php" class="text-blue-600 font-bold hover:underline mt-2 inline-block">Book your
                    first appointment</a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500">
                            <tr>
                                <th class="px-6 py-4">Date & Time</th>
                                <th class="px-6 py-4">Doctor</th>
                                <th class="px-6 py-4">Reason</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($appointments as $appt): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">
                                            <?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?>
                                        </div>
                                        <div class="text-xs text-blue-600">
                                            <?php echo date('h:i A', strtotime($appt['appointment_date'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">
                                            <?php echo htmlspecialchars($appt['doctor_name']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($appt['specialization']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 max-w-sm truncate"
                                        title="<?php echo htmlspecialchars($appt['description']); ?>">
                                        <?php echo htmlspecialchars($appt['description']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'confirmed' => 'bg-green-100 text-green-700',
                                            'completed' => 'bg-blue-100 text-blue-700',
                                            'cancelled' => 'bg-red-100 text-red-700'
                                        ];
                                        $color = $status_colors[$appt['status']] ?? 'bg-gray-100';
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $color; ?>">
                                            <?php echo ucfirst($appt['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>