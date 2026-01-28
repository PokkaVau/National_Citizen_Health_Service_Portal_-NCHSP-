<?php
require('../config/db.php');
require('../auth_session.php');
check_doctor_login();

$admin_id = $_SESSION['admin_id'];
$message = "";
$error = "";

// Get Doctor Info
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

// Handle Profile Picture
if (isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];

    if (in_array($file['type'], $allowed)) {
        $upload_dir = '../uploads/doctors/'; // Distinct directory
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'doc_' . $doctor_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("UPDATE doctors SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$target, $doctor_id]);
            $message = "Profile picture updated!";
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
            $stmt->execute([$doctor_id]);
            $doctor = $stmt->fetch();
        } else {
            $error = "Upload failed.";
        }
    } else {
        $error = "Invalid file type.";
    }
}

// Handle Bio Update
if (isset($_POST['update_profile'])) {
    $bio = trim($_POST['bio']);
    $specialization = trim($_POST['specialization']); // Allow updating specialization too? Maybe.

    $stmt = $pdo->prepare("UPDATE doctors SET bio = ?, specialization = ? WHERE id = ?");
    if ($stmt->execute([$bio, $specialization, $doctor_id])) {
        $message = "Profile updated successfully!";
        // Refresh
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch();
    } else {
        $error = "Update failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Doctor Portal</title>
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
                <h1 class="text-2xl font-bold text-slate-800">My Profile</h1>
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

            <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden max-w-3xl mx-auto">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <!-- Avatar Column -->
                        <div class="flex flex-col items-center gap-4">
                            <div
                                class="size-32 rounded-full border-4 border-indigo-50 overflow-hidden bg-indigo-50/50 flex items-center justify-center text-indigo-200">
                                <?php
                                $pic = $doctor['profile_picture'];
                                if ($pic): ?>
                                    <img src="<?php echo htmlspecialchars($pic); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-6xl">person</span>
                                <?php endif; ?>
                            </div>
                            <form action="" method="post" enctype="multipart/form-data">
                                <label
                                    class="cursor-pointer bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg font-medium text-sm hover:bg-indigo-100 transition-colors inline-block text-center">
                                    Change Photo
                                    <input type="file" name="profile_picture" class="hidden"
                                        onchange="this.form.submit()">
                                </label>
                            </form>
                        </div>

                        <!-- Details Column -->
                        <div class="flex-1 w-full">
                            <form action="" method="post" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($doctor['name']); ?>" disabled
                                        class="w-full px-3 py-2 border bg-slate-50 text-slate-500 rounded-lg cursor-not-allowed">
                                    <p class="text-xs text-slate-400 mt-1">Contact Admin to change name.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Specialization</label>
                                    <input type="text" name="specialization"
                                        value="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Bio / About Me</label>
                                    <textarea name="bio" rows="4" placeholder="Tell patients about your experience..."
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"><?php echo htmlspecialchars($doctor['bio'] ?? ''); ?></textarea>
                                </div>

                                <div class="pt-4">
                                    <button type="submit" name="update_profile"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>