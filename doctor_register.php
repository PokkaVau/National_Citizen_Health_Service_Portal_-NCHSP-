<?php
require('config/db.php');
require('auth_session.php');

$message = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $specialization = trim($_POST['specialization']);
    $mobile = trim($_POST['mobile']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($name) || empty($specialization) || empty($username) || empty($password)) {
        $message = "All fields are required!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // 1. Insert into admins table (role = 'doctor')
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'doctor')");
            $stmt->execute([$username, $hashed_password]);
            $admin_id = $pdo->lastInsertId();

            // 2. Insert into doctors table
            // Note: doctors table doesn't have mobile column based on add_doctor.php, but let's check schema if needed.
            // add_doctor.php: INSERT INTO doctors (admin_id, name, specialization) VALUES
            // wait, if I want to store mobile, does doctors table have it?
            // Let's assume for now I stick to add_doctor.php schema: admin_id, name, specialization.
            // If I want mobile, I might need to add it or ignore it.
            // Reviewing add_doctor.php again... it didn't use mobile.
            // But register.php (citizens) uses mobile.
            // Let's stick to what add_doctor.php does for now to avoid schema errors, 
            // OR I can add mobile to doctors table if I want.
            // The user request said "doctors can register themeselves with their necessary infos".
            // Infos usually include contact.
            // Let's check schema of doctors table? I can't check easily without running a script.
            // But I saw add_doctor.php and it only inserted name and specialization.
            // I'll stick to name and specialization for safety, and maybe username acts as contact?
            // actually, let's just insert what we know works.

            $stmt = $pdo->prepare("INSERT INTO doctors (admin_id, name, specialization) VALUES (?, ?, ?)");
            $stmt->execute([$admin_id, $name, $specialization]);

            $pdo->commit();

            // Notify Admins
            require_once 'config/notifications.php';
            $adminStmt = $pdo->query("SELECT id FROM admins WHERE role IN ('super_admin', 'medical_officer')");
            $admins = $adminStmt->fetchAll();
            foreach ($admins as $admin) {
                createNotification($pdo, $admin['id'], 'admin', "New doctor registration: Dr. " . $name, 'warning', '/dbms/admin/manage_doctors.php');
            }

            // Redirect to login (root)
            header("Location: login.php?doctor_registered=true");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) { // Duplicate entry
                $message = "Username already exists!";
            } else {
                $message = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            /* Greenish tint for doctors */
            min-height: 100vh;
        }

        .card-shadow {
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08), 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(22, 163, 74, 0.15);
            /* Green shadow */
        }

        .btn-register {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(22, 163, 74, 0.3);
        }

        .floating-label {
            position: absolute;
            top: -10px;
            left: 12px;
            background: white;
            padding: 0 8px;
            font-size: 12px;
            color: #16a34a;
            font-weight: 600;
            z-index: 10;
        }

        .password-strength {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 4px;
        }

        .strength-meter {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">

    <div class="relative bg-white rounded-2xl card-shadow w-full max-w-2xl overflow-hidden border border-gray-100">
        <!-- Decorative header -->
        <div class="h-3 bg-gradient-to-r from-green-500 via-emerald-600 to-teal-600"></div>

        <div class="px-10 pt-8 pb-4">
            <a href="index.php"
                class="inline-flex items-center text-sm text-gray-500 hover:text-green-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Home
            </a>
        </div>

        <div class="px-10 pb-10">
            <!-- Header -->
            <div class="text-center mb-10">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full shadow-lg mb-6">
                    <i class="fas fa-user-md text-white text-3xl"></i>
                </div>
                <h1
                    class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-800 bg-clip-text text-transparent">
                    Doctor Registration
                </h1>
                <p class="text-gray-600 mt-2 font-medium">Join our network of healthcare professionals</p>
            </div>

            <!-- Error Message -->
            <?php if ($message): ?>
                <div
                    class="flex items-center gap-3 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 text-red-700 p-4 rounded-xl mb-8 font-medium animate-pulse">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                    <div class="flex-1">
                        <p class="font-semibold">Registration Error</p>
                        <p class="text-sm"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="" method="post" class="space-y-8">

                <!-- Professional Info -->
                <div class="relative border border-gray-200 rounded-xl p-6 bg-gradient-to-br from-gray-50 to-white">
                    <div class="floating-label">Professional Information</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-user-md mr-2 text-green-500"></i>
                                Full Name
                            </label>
                            <input type="text" name="name" required
                                class="w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none"
                                placeholder="Dr. John Doe">
                        </div>

                        <!-- Specialization -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-stethoscope mr-2 text-green-500"></i>
                                Specialization
                            </label>
                            <input type="text" name="specialization" required
                                class="w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none"
                                placeholder="Cardiologist">
                        </div>

                        <!-- Mobile (Optional display only if not stored, or maybe we can store in future?) -->
                        <!-- I'll include it in form but maybe not save it if DB doesn't support it yet, 
                              User asked for "necessary infos". 
                              Wait, I should check if I can save it. 
                              The 'doctors' table in 'add_doctor.php' only had admin_id, name, specialization.
                              I will keep it in UI but if I can't save it, I'll just ignore it for now or assume I'll add column later.
                               actually, better not to mislead user. I'll omit mobile if I can't save it.
                              Let's stick to Name, Specialization, Username, Password.
                          -->
                    </div>
                </div>

                <!-- Account Security -->
                <div class="relative border border-gray-200 rounded-xl p-6 bg-gradient-to-br from-green-50 to-white">
                    <div class="floating-label">Account Setup</div>

                    <div class="space-y-6 mt-2">
                        <!-- Username -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-id-badge mr-2 text-green-500"></i>
                                Username
                            </label>
                            <input type="text" name="username" required
                                class="w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none"
                                placeholder="dr.johndoe">
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-lock mr-2 text-green-500"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" name="password" required id="password-input"
                                    class="w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none"
                                    placeholder="Create a strong password">
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-meter" id="password-strength"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="register"
                    class="w-full btn-register text-white font-bold py-4 rounded-xl transition-all duration-300">
                    <i class="fas fa-user-plus mr-2"></i>
                    Register as Doctor
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    Already registered?
                    <a href="admin/login.php" class="text-green-600 font-semibold hover:underline">
                        Log In to Portal
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password-input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Strength meter
        document.getElementById('password-input').addEventListener('input', function () {
            const val = this.value;
            let strength = 0;
            if (val.length >= 8) strength += 25;
            if (/[A-Z]/.test(val)) strength += 25;
            if (/[0-9]/.test(val)) strength += 25;
            if (/[^A-Za-z0-9]/.test(val)) strength += 25;

            const meter = document.getElementById('password-strength');
            meter.style.width = strength + '%';
            if (strength < 50) meter.style.backgroundColor = '#ef4444';
            else if (strength < 75) meter.style.backgroundColor = '#f59e0b';
            else meter.style.backgroundColor = '#22c55e';
        });
    </script>
</body>

</html>