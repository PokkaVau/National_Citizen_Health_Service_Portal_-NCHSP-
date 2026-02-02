<?php
require('../config/db.php');
require('../auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];
$hospital_id = isset($_GET['hospital_id']) ? $_GET['hospital_id'] : null;
$message = "";
$error = "";

if (!$hospital_id) {
    header("Location: nearby_hospitals.php");
    exit();
}

// Fetch Hospital Info
$stmt = $pdo->prepare("SELECT * FROM hospitals WHERE id = ?");
$stmt->execute([$hospital_id]);
$hospital = $stmt->fetch();

if (!$hospital) {
    $error = "Hospital not found.";
}

// Handle Booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_blood'])) {
    $blood_group = $_POST['blood_group'];
    $units = (int) $_POST['units'];

    if ($units > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO blood_bookings (user_id, hospital_id, blood_group, units, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $hospital_id, $blood_group, $units]);

            // Redirect to dashboard with success
            header("Location: ../dashboard.php?msg=booking_success");
            exit();
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Units must be greater than 0.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Blood - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-8">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900">Book Blood</h1>
            <p class="text-slate-500">at
                <?php echo htmlspecialchars($hospital['name']); ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="book_blood" value="1">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Blood Group</label>
                <select name="blood_group" required
                    class="w-full rounded-lg border-slate-300 focus:ring-red-500 focus:border-red-500">
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Number of Bags (Units)</label>
                <input type="number" name="units" min="1" max="10" required
                    class="w-full rounded-lg border-slate-300 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="flex gap-4">
                <a href="nearby_hospitals.php"
                    class="w-1/2 flex items-center justify-center px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="w-1/2 flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                    Confirm Booking
                </button>
            </div>
        </form>
    </div>
</body>

</html>