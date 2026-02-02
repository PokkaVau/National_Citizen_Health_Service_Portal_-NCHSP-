<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// Handle Profile Picture Upload
if (isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (in_array($file['type'], $allowed_types)) {
        $upload_dir = 'uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Update DB
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            if ($stmt->execute([$target_path, $user_id])) {
                $message = "Profile picture updated successfully!";
            } else {
                $error = "Database update failed.";
            }
        } else {
            $error = "Failed to upload file.";
        }
    } else {
        $error = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
    }
}

// Handle details update
if (isset($_POST['update_details'])) {
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $blood_type = $_POST['blood_type'];

    $stmt = $pdo->prepare("UPDATE users SET weight = ?, height = ?, blood_type = ? WHERE id = ?");
    if ($stmt->execute([$weight, $height, $blood_type, $user_id])) {
        $message = "Health details updated successfully!";
    } else {
        $error = "Failed to update details.";
    }
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Calculate age from DOB
$dob = new DateTime($user['dob']);
$today = new DateTime();
$age = $dob->diff($today)->y;

// Calculate BMI
$weight = $user['weight'];
$height_m = $user['height'] / 100;
$bmi = $weight / ($height_m * $height_m);
$bmi_category = "";
$bmi_color = "";

if ($bmi < 18.5) {
    $bmi_category = "Underweight";
    $bmi_color = "text-blue-600";
} elseif ($bmi < 25) {
    $bmi_category = "Normal";
    $bmi_color = "text-green-600";
} elseif ($bmi < 30) {
    $bmi_category = "Overweight";
    $bmi_color = "text-orange-600";
} else {
    $bmi_category = "Obese";
    $bmi_color = "text-red-600";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HealthPortal</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
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

        .profile-image-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .profile-image-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(19, 127, 236, 0.2);
        }

        .health-stat {
            position: relative;
            overflow: hidden;
        }

        .health-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .health-stat:hover::before {
            opacity: 1;
        }

        .bmi-meter {
            height: 8px;
            border-radius: 4px;
            background: linear-gradient(90deg,
                    #3b82f6 0%,
                    #10b981 25%,
                    #f59e0b 50%,
                    #ef4444 75%,
                    #dc2626 100%);
            position: relative;
            margin: 1.5rem 0;
        }

        .bmi-indicator {
            position: absolute;
            top: -4px;
            width: 4px;
            height: 16px;
            background: white;
            border: 2px solid var(--color-primary);
            border-radius: 2px;
            transform: translateX(-50%);
            transition: left 1s ease;
        }

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.15);
        }

        .success-message {
            animation: slideDown 0.5s ease-out;
        }

        .error-message {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .progress-ring {
            stroke-dasharray: 283;
            stroke-dashoffset: 283;
            transition: stroke-dashoffset 1s ease;
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

        .blood-type-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            font-weight: bold;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
        }

        .blood-type-badge:hover {
            transform: scale(1.1) rotate(5deg);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Navbar -->
    <nav class="glass-card sticky top-0 z-20 border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php"
                        class="flex items-center gap-3 text-slate-600 hover:text-primary transition-colors group">
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-r from-primary/10 to-blue-500/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-primary">arrow_back</span>
                        </div>
                        <div>
                            <span class="font-medium">Back to Dashboard</span>
                            <p class="text-xs text-slate-500">Return to your health overview</p>
                        </div>
                    </a>
                </div>

                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold bg-gradient-to-r from-primary to-grey-600 bg-clip-text text-black">
                        My Health Profile
                    </h1>
                    <div class="relative">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-ping absolute"></div>
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-8">
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="success-message glass-card rounded-2xl border border-green-200/50 overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <i class="fas fa-check text-white text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-900">Success!</p>
                        <p class="text-sm text-green-700"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                    <button onclick="this.parentElement.parentElement.style.display='none'"
                        class="text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message glass-card rounded-2xl border border-red-200/50 overflow-hidden">
                <div class="bg-gradient-to-r from-red-50 to-pink-50 p-4 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-900">Error</p>
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                    <button onclick="this.parentElement.parentElement.style.display='none'"
                        class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Profile Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Profile Card -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Profile Header Card -->
                <div class="glass-card rounded-2xl border border-slate-200/50 overflow-hidden">
                    <div class="h-48 bg-gradient-to-r from-primary via-blue-500 to-indigo-600 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        <div class="absolute top-6 left-6">
                            <span
                                class="text-white/80 text-sm font-medium bg-white/10 backdrop-blur-sm px-3 py-1 rounded-full">
                                <i class="fas fa-id-card mr-1"></i>
                                Citizen Profile
                            </span>
                        </div>
                    </div>

                    <div class="relative px-8 pb-8">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end -mt-16 mb-8">
                            <div class="flex items-end gap-6">
                                <div class="relative">
                                    <div class="relative profile-image-hover">
                                        <div
                                            class="w-32 h-32 rounded-2xl border-4 border-white shadow-xl overflow-hidden bg-gradient-to-br from-primary/20 to-blue-500/20">
                                            <?php if (!empty($user['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>"
                                                    alt="Profile" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <i class="fas fa-user text-primary text-5xl"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <form action="" method="post" enctype="multipart/form-data"
                                            class="absolute -bottom-2 -right-2">
                                            <label class="cursor-pointer group">
                                                <div
                                                    class="w-10 h-10 rounded-full gradient-primary flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                                                    <i class="fas fa-camera text-white text-sm"></i>
                                                </div>
                                                <input type="file" name="profile_picture" class="hidden"
                                                    onchange="this.form.submit()">
                                            </label>
                                        </form>
                                    </div>

                                    <!-- Status Indicator -->
                                    <div
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h2 class="text-3xl font-bold text-slate-900 mb-2">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </h2>
                                    <div class="flex flex-wrap items-center gap-4">
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <i class="fas fa-calendar-alt text-primary"></i>
                                            <span class="font-medium"><?php echo $age; ?> years</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <i class="fas fa-venus-mars text-primary"></i>
                                            <span class="font-medium">Adult</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <i class="fas fa-shield-check text-green-500"></i>
                                            <span class="font-medium">Verified Citizen</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                                <button
                                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-300 text-sm font-medium flex items-center gap-2">
                                    <i class="fas fa-print"></i>
                                    Print ID
                                </button>
                                <button
                                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-300 text-sm font-medium flex items-center gap-2">
                                    <i class="fas fa-share-alt"></i>
                                    Share
                                </button>
                            </div>
                        </div>

                        <!-- Personal Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div
                                class="health-stat bg-gradient-to-r from-slate-50 to-white p-5 rounded-xl border border-slate-200/50">
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center">
                                        <i class="fas fa-fingerprint"></i>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Voter
                                            ID</label>
                                        <p class="text-lg font-bold text-slate-900 font-mono">
                                            <?php echo htmlspecialchars($user['voter_id']); ?>
                                        </p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Unique government identifier
                                </p>
                            </div>

                            <div
                                class="health-stat bg-gradient-to-r from-slate-50 to-white p-5 rounded-xl border border-slate-200/50">
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 flex items-center justify-center">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Mobile
                                            Number</label>
                                        <p class="text-lg font-bold text-slate-900">
                                            <?php echo htmlspecialchars($user['mobile']); ?>
                                        </p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500">
                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                    Verified and active
                                </p>
                            </div>
                        </div>

                        <!-- DOB Card -->
                        <div
                            class="health-stat bg-gradient-to-r from-slate-50 to-white p-5 rounded-xl border border-slate-200/50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 flex items-center justify-center">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <div class="flex-1">
                                    <label
                                        class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Date
                                        of Birth</label>
                                    <p class="text-lg font-bold text-slate-900">
                                        <?php echo htmlspecialchars(date('F j, Y', strtotime($user['dob']))); ?>
                                    </p>
                                    <p class="text-sm text-slate-600">
                                        Born on a <?php echo date('l', strtotime($user['dob'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-indigo-600"><?php echo $age; ?></div>
                                    <div class="text-xs text-slate-500">Years old</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Health Details Form -->
                <div class="glass-card rounded-2xl border border-slate-200/50 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center">
                                    <i class="fas fa-heartbeat text-white text-xl"></i>
                                </div>
                                <span>Physical Health Details</span>
                            </h3>
                            <p class="text-slate-500 mt-2">Update your current health metrics</p>
                        </div>
                        <div class="hidden sm:block">
                            <div class="text-xs font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full">
                                Last updated: Today
                            </div>
                        </div>
                    </div>

                    <form action="" method="post" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <!-- Weight Input -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-semibold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-weight-scale text-blue-500"></i>
                                        Weight
                                    </label>
                                    <span class="text-xs text-slate-500">in kg</span>
                                </div>
                                <div class="relative">
                                    <input type="number" step="0.1" name="weight"
                                        value="<?php echo htmlspecialchars($user['weight']); ?>"
                                        class="input-field w-full pl-12 pr-4 py-4 bg-gradient-to-r from-slate-50 to-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all duration-300">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center">
                                            <i class="fas fa-weight"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Healthy range: 50-80 kg
                                </div>
                            </div>

                            <!-- Height Input -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-semibold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-ruler-vertical text-purple-500"></i>
                                        Height
                                    </label>
                                    <span class="text-xs text-slate-500">in cm</span>
                                </div>
                                <div class="relative">
                                    <input type="number" step="1" name="height"
                                        value="<?php echo htmlspecialchars($user['height']); ?>"
                                        class="input-field w-full pl-12 pr-4 py-4 bg-gradient-to-r from-slate-50 to-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all duration-300">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 flex items-center justify-center">
                                            <i class="fas fa-arrows-up-down"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Average adult: 160-180 cm
                                </div>
                            </div>

                            <!-- Blood Type Select -->
                            <div class="space-y-4">
                                <label class="block text-sm font-semibold text-slate-900 flex items-center gap-2">
                                    <i class="fas fa-droplet text-red-500"></i>
                                    Blood Type
                                </label>
                                <div class="relative">
                                    <select name="blood_type"
                                        class="input-field w-full pl-12 pr-10 py-4 bg-gradient-to-r from-slate-50 to-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none appearance-none transition-all duration-300">
                                        <?php
                                        $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($blood_types as $bt) {
                                            $selected = ($user['blood_type'] == $bt) ? 'selected' : '';
                                            $color_class = str_contains($bt, '+') ? 'bg-red-500' : 'bg-red-400';
                                            echo "<option value='$bt' $selected>$bt</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-50 to-red-100 text-red-600 flex items-center justify-center">
                                            <i class="fas fa-heart-pulse"></i>
                                        </div>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-slate-400"></i>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Critical for emergencies
                                </div>
                            </div>
                        </div>

                        <!-- Current Blood Type Display -->
                        <div class="p-5 bg-gradient-to-r from-red-50 to-pink-50 rounded-xl border border-red-200/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="blood-type-badge gradient-primary shadow-lg">
                                        <?php echo htmlspecialchars($user['blood_type']); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">Current Blood Type</p>
                                        <p class="text-sm text-slate-600">This information is vital for medical
                                            emergencies</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-500">Compatibility:</p>
                                    <p class="text-sm font-semibold text-slate-900">
                                        <?php
                                        $compatibility = [
                                            'A+' => 'A+, AB+',
                                            'A-' => 'A+, A-, AB+, AB-',
                                            'B+' => 'B+, AB+',
                                            'B-' => 'B+, B-, AB+, AB-',
                                            'AB+' => 'AB+',
                                            'AB-' => 'AB+, AB-',
                                            'O+' => 'O+, A+, B+, AB+',
                                            'O-' => 'All blood types'
                                        ];
                                        echo $compatibility[$user['blood_type']] ?? 'Universal';
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-6 border-t border-slate-200/50 flex items-center justify-between">
                            <div class="text-sm text-slate-500">
                                <i class="fas fa-shield-alt text-primary mr-2"></i>
                                Your health data is encrypted and secure
                            </div>
                            <button type="submit" name="update_details"
                                class="gradient-primary text-white px-8 py-3.5 rounded-xl font-semibold text-sm hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center gap-3 group">
                                <i class="fas fa-save group-hover:rotate-12 transition-transform"></i>
                                Save Health Details
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Health Stats -->
            <div class="space-y-8">
                <!-- BMI Calculator -->
                <div class="glass-card rounded-2xl border border-slate-200/50 p-6">
                    <h4 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fas fa-calculator text-primary"></i>
                        BMI Calculator
                    </h4>

                    <div class="text-center mb-6">
                        <div class="relative w-40 h-40 mx-auto mb-4">
                            <svg class="w-full h-full" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8" />
                                <circle class="progress-ring" cx="50" cy="50" r="45" fill="none" stroke="#3b82f6"
                                    stroke-width="8" stroke-linecap="round"
                                    stroke-dashoffset="<?php echo 283 - (283 * min($bmi, 40) / 40); ?>" />
                                <text x="50" y="50" text-anchor="middle" dy="0.3em" fill="#1e293b" font-size="16"
                                    font-weight="bold">
                                    <?php echo number_format($bmi, 1); ?>
                                </text>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500">Body Mass Index</p>
                        <p class="text-lg font-bold <?php echo $bmi_color; ?> mt-2">
                            <?php echo $bmi_category; ?>
                        </p>
                    </div>

                    <div class="bmi-meter">
                        <div class="bmi-indicator" style="left: <?php echo min($bmi, 40) * 2.5; ?>%;"></div>
                    </div>

                    <div class="grid grid-cols-4 text-xs text-slate-500">
                        <div>Under</div>
                        <div class="text-center">Normal</div>
                        <div class="text-center">Over</div>
                        <div class="text-right">Obese</div>
                    </div>
                </div>

                <!-- Health Stats -->
                <div class="glass-card rounded-2xl border border-slate-200/50 p-6">
                    <h4 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-line text-primary"></i>
                        Health Summary
                    </h4>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-blue-100/50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                    <i class="fas fa-weight text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700">Weight</p>
                                    <p class="text-lg font-bold text-slate-900"><?php echo $user['weight']; ?> kg</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500">Status</p>
                                <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                    Normal
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-purple-100/50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                    <i class="fas fa-ruler-vertical text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700">Height</p>
                                    <p class="text-lg font-bold text-slate-900"><?php echo $user['height']; ?> cm</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500">Percentile</p>
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                                    75th
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="glass-card rounded-2xl border border-slate-200/50 p-6">
                    <h4 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fas fa-bolt text-yellow-500"></i>
                        Quick Actions
                    </h4>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="reports.php"
                            class="p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200 text-center hover:border-primary hover:shadow-md transition-all duration-300 group">
                            <div
                                class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-green-50 to-green-100 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-900">View Reports</p>
                        </a>

                        <a href="book_appointment.php"
                            class="p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200 text-center hover:border-primary hover:shadow-md transition-all duration-300 group">
                            <div
                                class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-900">Book Visit</p>
                        </a>

                        <a href="#"
                            class="p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200 text-center hover:border-primary hover:shadow-md transition-all duration-300 group">
                            <div
                                class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-download"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-900">Export Data</p>
                        </a>

                        <a href="#"
                            class="p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200 text-center hover:border-primary hover:shadow-md transition-all duration-300 group">
                            <div
                                class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-orange-50 to-orange-100 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-print"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-900">Print ID</p>
                        </a>
                    </div>
                </div>

                <!-- Emergency Info -->
                <div
                    class="glass-card rounded-2xl border border-red-200/50 overflow-hidden bg-gradient-to-r from-red-50 to-pink-50">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">Emergency Info</h4>
                                <p class="text-xs text-slate-600">Critical medical details</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-700">Blood Type:</span>
                                <span class="font-bold text-red-600"><?php echo $user['blood_type']; ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-700">Age:</span>
                                <span class="font-bold text-slate-900"><?php echo $age; ?> years</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-700">Allergies:</span>
                                <span class="font-bold text-green-600">None reported</span>
                            </div>
                        </div>

                        <button
                            class="w-full mt-6 py-2.5 bg-white text-red-600 rounded-xl border border-red-200 hover:bg-red-50 transition-colors text-sm font-medium flex items-center justify-center gap-2">
                            <i class="fas fa-phone-alt"></i>
                            Emergency Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize BMI progress ring
        document.addEventListener('DOMContentLoaded', function () {
            const bmi = <?php echo $bmi; ?>;
            const ring = document.querySelector('.progress-ring');

            if (ring) {
                const circle = ring.querySelector('circle');
                const radius = circle.r.baseVal.value;
                const circumference = radius * 2 * Math.PI;

                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = circumference;

                const offset = circumference - (Math.min(bmi, 40) / 40 * circumference);
                setTimeout(() => {
                    circle.style.strokeDashoffset = offset;
                }, 300);
            }

            // Animate BMI indicator
            const indicator = document.querySelector('.bmi-indicator');
            if (indicator) {
                setTimeout(() => {
                    indicator.style.left = `${Math.min(bmi, 40) * 2.5}%`;
                }, 500);
            }

            // Form validation
            const form = document.querySelector('form');
            const weightInput = form.querySelector('input[name="weight"]');
            const heightInput = form.querySelector('input[name="height"]');

            form.addEventListener('submit', function (e) {
                const weight = parseFloat(weightInput.value);
                const height = parseFloat(heightInput.value);

                if (weight < 20 || weight > 200) {
                    e.preventDefault();
                    alert('Please enter a valid weight between 20-200 kg');
                    weightInput.focus();
                    return;
                }

                if (height < 100 || height > 250) {
                    e.preventDefault();
                    alert('Please enter a valid height between 100-250 cm');
                    heightInput.focus();
                    return;
                }
            });

            // Real-time BMI calculation
            function calculateBMI() {
                const weight = parseFloat(weightInput.value) || <?php echo $user['weight']; ?>;
                const height = parseFloat(heightInput.value) || <?php echo $user['height']; ?>;
                const heightM = height / 100;
                const bmi = weight / (heightM * heightM);

                // Update BMI indicator position
                if (indicator) {
                    indicator.style.left = `${Math.min(bmi, 40) * 2.5}%`;
                }
            }

            weightInput.addEventListener('input', calculateBMI);
            heightInput.addEventListener('input', calculateBMI);
        });
    </script>
</body>

</html>