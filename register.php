<?php
require('config/db.php');
require('auth_session.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $dob = $_POST['dob'];
    $voter_id = trim($_POST['voter_id']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $blood_type = $_POST['blood_type'];

    // Basic validation
    if (empty($name) || empty($dob) || empty($voter_id) || empty($mobile) || empty($password) || empty($weight) || empty($height) || empty($blood_type)) {
        $message = "All fields are required!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, dob, voter_id, mobile, password, weight, height, blood_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $dob, $voter_id, $mobile, $hashed_password, $weight, $height, $blood_type]);

            // Redirect to login on success
            header("Location: login.php?registered=true");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $message = "Voter ID or Mobile Number already registered!";
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
    <title>Register - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #e6f0ff 100%);
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
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.15);
        }

        .step-indicator {
            position: relative;
            z-index: 1;
        }

        .step-indicator::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(to right, #3b82f6 0%, #e5e7eb 100%);
            z-index: -1;
        }

        .form-section {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-register {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .floating-label {
            position: absolute;
            top: -10px;
            left: 12px;
            background: white;
            padding: 0 8px;
            font-size: 12px;
            color: #3b82f6;
            font-weight: 600;
            z-index: 10;
        }

        .info-bubble {
            position: relative;
        }

        .info-bubble:hover .info-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .info-tooltip {
            opacity: 0;
            visibility: hidden;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 8px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            width: 280px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 50;
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

        .character-count {
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <!-- Background decorative elements -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-blue-200 rounded-full -translate-x-1/2 -translate-y-1/2 opacity-20">
    </div>
    <div
        class="absolute bottom-0 right-0 w-96 h-96 bg-blue-100 rounded-full translate-x-1/3 translate-y-1/3 opacity-20">
    </div>

    <div class="relative bg-white rounded-2xl card-shadow w-full max-w-2xl overflow-hidden border border-gray-100">
        <!-- Decorative header -->
        <div class="h-3 bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600"></div>

        <div class="px-10 pt-8 pb-4">
            <a href="index.php"
                class="inline-flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Home
            </a>
        </div>

        <div class="px-10 pb-10">
            <!-- Header with icon and progress -->
            <div class="text-center mb-10">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full shadow-lg mb-6">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                    Citizen Registration
                </h1>
                <p class="text-gray-600 mt-2 font-medium">Join the National Health Service Portal</p>

                <!-- Step indicator -->
                <div class="mt-8 relative step-indicator">
                    <div class="flex justify-between max-w-md mx-auto px-4">
                        <div class="flex flex-col items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                                1
                            </div>
                            <span class="text-xs font-medium text-blue-600 mt-2">Personal Info</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                2
                            </div>
                            <span class="text-xs font-medium text-gray-500 mt-2">Health Details</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                3
                            </div>
                            <span class="text-xs font-medium text-gray-500 mt-2">Account Setup</span>
                        </div>
                    </div>
                </div>
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
                    <button onclick="this.parentElement.style.display='none'" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="" method="post" class="space-y-8 form-section">
                <!-- Section 1: Personal Information -->
                <div class="relative border border-gray-200 rounded-xl p-6 bg-gradient-to-br from-gray-50 to-white">
                    <div class="floating-label">Personal Information</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-user mr-2 text-blue-500"></i>
                                Full Name
                            </label>
                            <div class="relative">
                                <input type="text" name="name" required
                                    class="pl-10 w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    placeholder="John Doe">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>
                                Date of Birth
                            </label>
                            <div class="relative">
                                <input type="date" name="dob" required
                                    class="pl-10 w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-birthday-cake text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Voter ID -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-id-card mr-2 text-blue-500"></i>
                                    Voter ID
                                </label>
                                <div class="info-bubble relative">
                                    <button type="button" class="text-gray-400 hover:text-blue-500">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <div class="info-tooltip">
                                        <p class="text-sm font-medium text-gray-900 mb-1">Voter ID Format</p>
                                        <p class="text-xs text-gray-600">Enter your government-issued Voter ID number.
                                            This will be used as a unique identifier.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="text" name="voter_id" required
                                    class="pl-10 w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    placeholder="VOT12345678">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-fingerprint text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Number -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-mobile-alt mr-2 text-blue-500"></i>
                                Mobile Number
                            </label>
                            <div class="relative">
                                <input type="text" name="mobile" required
                                    class="pl-10 w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    placeholder="9876543210">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Health Information -->
                <div class="relative border border-gray-200 rounded-xl p-6 bg-gradient-to-br from-blue-50 to-white">
                    <div class="floating-label">Health Information</div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-2">
                        <!-- Weight -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-weight-scale mr-2 text-blue-500"></i>
                                Weight (kg)
                            </label>
                            <div class="relative">
                                <input type="number" step="0.01" name="weight" required
                                    class="pl-10 w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    placeholder="70.5">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-weight text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Height -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-ruler-vertical mr-2 text-blue-500"></i>
                                Height (cm)
                            </label>
                            <div class="relative">
                                <input type="number" step="0.01" name="height" required
                                    class="pl-10 w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    placeholder="175.0">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-arrows-up-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Blood Type -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-droplet mr-2 text-red-500"></i>
                                Blood Type
                            </label>
                            <div class="relative">
                                <select name="blood_type" required
                                    class="pl-10 appearance-none w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none bg-white">
                                    <option value="" disabled selected>Select Blood Type</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-heart-pulse text-gray-400"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Account Security -->
                <div class="relative border border-gray-200 rounded-xl p-6 bg-gradient-to-br from-green-50 to-white">
                    <div class="floating-label">Account Security</div>

                    <div class="space-y-6 mt-2">
                        <!-- Password -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-lock mr-2 text-blue-500"></i>
                                    Password
                                </label>
                                <div class="character-count text-xs" id="password-count">0 characters</div>
                            </div>
                            <div class="relative">
                                <input type="password" name="password" required id="password-input"
                                    class="pl-10 w-full px-4 py-3.5 border border-gray-300 rounded-xl input-focus focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    placeholder="Create a strong password">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-gray-400"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                        id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Password Strength Meter -->
                            <div class="password-strength">
                                <div class="strength-meter" id="password-strength"></div>
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-1">
                                <i class="fas fa-info-circle"></i>
                                Use at least 8 characters with mix of letters, numbers & symbols
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="flex items-start gap-3 p-4 bg-blue-50 rounded-lg">
                            <input type="checkbox" id="terms" required
                                class="mt-1 text-blue-600 focus:ring-blue-500 rounded">
                            <label for="terms" class="text-sm text-gray-700">
                                I agree to the <a href="#" class="text-blue-600 font-medium hover:underline">Terms of
                                    Service</a>
                                and <a href="#" class="text-blue-600 font-medium hover:underline">Privacy Policy</a>.
                                I confirm that all information provided is accurate.
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="register"
                    class="w-full btn-register text-white font-bold py-4 rounded-xl transition-all duration-300 mt-4">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Citizen Account
                </button>
            </form>

            <!-- Footer Links -->
            <div class="mt-10 pt-8 border-t border-gray-200">
                <div class="text-center space-y-4">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="login.php"
                            class="text-blue-600 font-semibold hover:text-blue-800 hover:underline ml-1 inline-flex items-center gap-1">
                            Sign In Here
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
                        <div class="flex items-center gap-2 text-gray-500">
                            <i class="fas fa-shield-alt text-blue-500"></i>
                            <span>Secure & Encrypted Registration</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500">
                            <i class="fas fa-clock text-blue-500"></i>
                            <span>Quick Process • 2 Minutes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password-input');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password strength meter
        const passwordInput = document.getElementById('password-input');
        const strengthMeter = document.getElementById('password-strength');
        const passwordCount = document.getElementById('password-count');

        passwordInput.addEventListener('input', function () {
            const password = this.value;
            const length = password.length;

            // Update character count
            passwordCount.textContent = `${length} characters`;

            // Calculate strength
            let strength = 0;
            let meterColor = '#ef4444'; // red

            if (length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;

            // Update meter
            strengthMeter.style.width = `${strength}%`;

            // Update color based on strength
            if (strength < 50) {
                meterColor = '#ef4444'; // red
            } else if (strength < 75) {
                meterColor = '#f59e0b'; // amber
            } else {
                meterColor = '#10b981'; // green
            }

            strengthMeter.style.backgroundColor = meterColor;
        });

        // Date of Birth validation (must be at least 18 years old)
        const dobInput = document.querySelector('input[name="dob"]');
        const today = new Date();
        const minDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

        dobInput.max = minDate.toISOString().split('T')[0];

        // Mobile number validation
        const mobileInput = document.querySelector('input[name="mobile"]');
        mobileInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });

        // Voter ID validation (alphanumeric)
        const voterIdInput = document.querySelector('input[name="voter_id"]');
        voterIdInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });

        // Form submission feedback
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Account...';
            submitBtn.disabled = true;

            // Re-enable button after 5 seconds if form submission fails
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    </script>
</body>

</html>