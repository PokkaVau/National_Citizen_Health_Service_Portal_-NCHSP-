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

// Handle Bio and Details Update
if (isset($_POST['update_profile'])) {
    $bio = trim($_POST['bio']);
    $education = trim($_POST['education']);
    $expertise = trim($_POST['expertise']);
    $available_hours = trim($_POST['available_hours']);

    $stmt = $pdo->prepare("UPDATE doctors SET bio = ?, education = ?, expertise = ?, available_hours = ? WHERE id = ?");
    if ($stmt->execute([$bio, $education, $expertise, $available_hours, $doctor_id])) {
        $message = "Profile updated successfully!";
        // Refresh
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch();
    } else {
        $error = "Update failed.";
    }
}

// Handle Name Change Request
if (isset($_POST['request_name_change'])) {
    $requested_name = trim($_POST['requested_name']);
    if (!empty($requested_name)) {
        // Check for existing pending request
        $stmt = $pdo->prepare("SELECT id FROM doctor_name_requests WHERE doctor_id = ? AND status = 'pending'");
        $stmt->execute([$doctor_id]);
        if ($stmt->fetch()) {
            $error = "You already have a pending name change request.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO doctor_name_requests (doctor_id, current_name, requested_name) VALUES (?, ?, ?)");
            if ($stmt->execute([$doctor_id, $doctor['name'], $requested_name])) {
                $message = "Name change request submitted for admin approval.";
            } else {
                $error = "Failed to submit request.";
            }
        }
    }
}

// Fetch Pending Request Status
$stmt = $pdo->prepare("SELECT * FROM doctor_name_requests WHERE doctor_id = ? AND status = 'pending'");
$stmt->execute([$doctor_id]);
$pending_request = $stmt->fetch();
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
                                $pic = $doctor['profile_picture'] ?? null;
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
                                    <div class="flex gap-2">
                                        <input type="text" value="<?php echo htmlspecialchars($doctor['name']); ?>"
                                            disabled
                                            class="w-full px-3 py-2 border bg-slate-50 text-slate-500 rounded-lg cursor-not-allowed">
                                        <button type="button"
                                            onclick="document.getElementById('name-request-modal').classList.remove('hidden')"
                                            class="bg-indigo-100 text-indigo-600 px-3 py-2 rounded-lg hover:bg-indigo-200 transition-colors text-sm font-medium whitespace-nowrap">
                                            Request Change
                                        </button>
                                    </div>
                                    <?php if ($pending_request): ?>
                                        <p class="text-xs text-amber-600 mt-1 font-medium flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">pending</span>
                                            Pending approval for:
                                            <?php echo htmlspecialchars($pending_request['requested_name']); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-xs text-slate-400 mt-1">Name can only be changed by Admin approval.
                                        </p>
                                    <?php endif; ?>
                                </div>



                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Education</label>
                                    <textarea name="education" rows="2" placeholder="e.g. MBBS, FCPS (Medicine)"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"><?php echo htmlspecialchars($doctor['education'] ?? ''); ?></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Expertise / Areas of
                                        Interest</label>
                                    <textarea name="expertise" rows="2" placeholder="e.g. Cardiology, Child Nutrition"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"><?php echo htmlspecialchars($doctor['expertise'] ?? ''); ?></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Most Often Available
                                        Times</label>
                                    <input type="text" name="available_hours" placeholder="e.g. Mon-Fri 5pm-9pm"
                                        value="<?php echo htmlspecialchars($doctor['available_hours'] ?? ''); ?>"
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
    <!-- Name Request Modal -->
    <div id="name-request-modal" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Request Name Change</h3>
            <form action="" method="post">
                <label class="block text-sm font-medium text-slate-700 mb-2">New Name</label>
                <input type="text" name="requested_name" required placeholder="Enter desired name"
                    class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none mb-4">
                <div class="flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('name-request-modal').classList.add('hidden')"
                        class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-medium">Cancel</button>
                    <button type="submit" name="request_name_change"
                        class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg font-medium">Submit
                        Request</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>