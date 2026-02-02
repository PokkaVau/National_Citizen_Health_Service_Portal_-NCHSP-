<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

// Fetch all doctors with their admin info (optional for future avatars etc, but currently just name/spec in doctors table)
// Actually doctors table has name and specialization. Admin table has login.
// We just need the doctors table.
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
    $error = "Error fetching doctors: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - NCHSP</title>
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
                <a href="index.php" class="text-gray-600 hover:text-blue-600 font-medium text-sm">Dashboard</a>
                <a href="my_appointments.php"
                    class="text-gray-600 hover:text-blue-600 font-medium text-sm">Appointments</a>
                <a href="logout.php" class="text-red-500 hover:text-red-700 font-medium text-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center gap-3 mb-8">
            <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
                <span class="material-symbols-outlined">supervisor_account</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Medical Specialists</h1>
                <p class="text-slate-500 text-sm">Connect with our experienced doctors</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($doctors as $doctor): ?>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4 mb-4">
                        <div
                            class="size-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl overflow-hidden">
                            <?php if (!empty($doctor['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars(str_replace('../', '', $doctor['profile_picture'])); ?>"
                                    class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($doctor['name'], 4, 1)); // Skip 'Dr. ' ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">
                                <?php echo htmlspecialchars($doctor['name']); ?>
                            </h3>
                            <p class="text-blue-600 text-sm font-medium">
                                <?php echo htmlspecialchars($doctor['specialization']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-4 text-sm text-gray-500 border-t pt-4">
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-green-500 text-lg">verified</span>
                            <span>Verified</span>
                        </div>
                        <div class="flex items-center gap-1 text-amber-500">
                            <span class="material-symbols-outlined text-lg">star</span>
                            <span>
                                <?php echo $doctor['avg_rating'] ? number_format($doctor['avg_rating'], 1) : 'New'; ?>
                                <span class="text-xs text-gray-400 ml-1">
                                    (<?php echo $doctor['review_count']; ?> reviews)
                                </span>
                            </span>
                        </div>
                    </div>

                    <a href="book_appointment.php"
                        class="mt-6 w-full flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 rounded-lg transition-colors">
                        Book Appointment
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>