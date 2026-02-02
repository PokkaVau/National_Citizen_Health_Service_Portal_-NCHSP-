<?php
require('config/db.php');

// Configure session timeout to 24 hours
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);

session_start();

$message = "";

if (isset($_POST['login'])) {
    $identifier = trim($_POST['identifier']); // Can be mobile or username
    $password = $_POST['password'];
    $role = $_POST['role']; // 'citizen' or 'admin'

    if ($role == 'citizen') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE mobile = ? OR voter_id = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Invalid Citizen credentials!";
        }
    } else if ($role == 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$identifier]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {

            // Check if doctor and status is pending OR rejected
            if ($admin['role'] == 'doctor') {
                $stmtDoc = $pdo->prepare("SELECT status FROM doctors WHERE admin_id = ?");
                $stmtDoc->execute([$admin['id']]);
                $doc = $stmtDoc->fetch();

                if ($doc && $doc['status'] == 'pending') {
                    $message = "Your account is pending admin approval. Please wait.";
                } else if ($doc && $doc['status'] == 'rejected') {
                    $message = "Your account application was rejected.";
                } else {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_role'] = $admin['role'];
                    header("Location: doctor/dashboard.php");
                    exit();
                }
            } else {
                // Not a doctor (e.g. super_admin, assistant, or hospital_rep)
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_role'] = $admin['role'];

                if ($admin['role'] == 'assistant') {
                    header("Location: doctor/dashboard.php");
                } elseif ($admin['role'] == 'hospital_representative') {
                    // Fetch Hospital Id from hospital_representatives using admin_id
                    $stmtRel = $pdo->prepare("SELECT * FROM hospital_representatives WHERE admin_id = ?");
                    $stmtRel->execute([$admin['id']]);
                    $link = $stmtRel->fetch();

                    if ($link) {
                        $_SESSION['hospital_id'] = $link['hospital_id'];
                        $_SESSION['rep_id'] = $link['id'];
                        $_SESSION['role'] = 'hospital_rep'; // Keep this for consistency if needed, or rely on admin_role

                        header("Location: hospital/dashboard.php");
                    } else {
                        $message = "Account exists but not assigned to any hospital.";
                        // Clear session to prevent partial login
                        session_unset();
                        session_destroy();
                    }
                } else {
                    header("Location: admin/dashboard.php");
                }
                exit();
            }
        } else {
            $message = "Invalid Admin credentials!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0e17 0%, #131827 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .cyber-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background:
                radial-gradient(circle at 10% 20%, rgba(0, 150, 255, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 90% 80%, rgba(0, 255, 200, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 50% 50%, rgba(120, 119, 198, 0.02) 0%, transparent 30%),
                linear-gradient(45deg, rgba(10, 14, 23, 0.95) 0%, rgba(19, 24, 39, 0.95) 100%);
        }

        .grid-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(0, 150, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 150, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 50px 50px;
            }
        }

        .cyber-glow {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .cyber-glow-element {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.3;
            animation: cyberFloat 20s infinite alternate ease-in-out;
        }

        .cyber-glow-element:nth-child(1) {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #0066ff 0%, transparent 70%);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .cyber-glow-element:nth-child(2) {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #00ffcc 0%, transparent 70%);
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }

        @keyframes cyberFloat {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(20px, 20px);
            }
        }

        .card-shadow {
            box-shadow:
                0 25px 100px rgba(0, 150, 255, 0.15),
                0 8px 40px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(0, 150, 255, 0.1),
                inset 0 0 50px rgba(0, 150, 255, 0.05);
            backdrop-filter: blur(20px);
            background: rgba(20, 25, 40, 0.85);
            border: 1px solid rgba(0, 150, 255, 0.2);
        }

        .input-focus {
            transition: all 0.3s ease;
            background: rgba(10, 15, 30, 0.8);
            border: 1px solid rgba(0, 150, 255, 0.3);
            color: #fff;
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow:
                0 20px 40px rgba(0, 150, 255, 0.2),
                0 0 0 4px rgba(0, 150, 255, 0.1),
                inset 0 0 20px rgba(0, 150, 255, 0.05);
            border-color: #00ffcc;
        }

        .role-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 150, 255, 0.3);
            background: rgba(10, 15, 30, 0.8);
        }

        .role-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow:
                0 20px 50px rgba(0, 150, 255, 0.2),
                0 0 30px rgba(0, 150, 255, 0.1);
            border-color: #00ffcc;
        }

        .role-card.selected {
            border-color: #00ffcc;
            background: linear-gradient(135deg, rgba(0, 150, 255, 0.15) 0%, rgba(0, 255, 200, 0.05) 100%);
            box-shadow:
                0 15px 40px rgba(0, 150, 255, 0.25),
                0 0 20px rgba(0, 150, 255, 0.1),
                inset 0 0 20px rgba(0, 150, 255, 0.05);
        }

        .btn-login {
            background: linear-gradient(135deg, #0066ff 0%, #00ffcc 100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-4px);
            box-shadow:
                0 25px 60px rgba(0, 150, 255, 0.5),
                0 0 40px rgba(0, 255, 200, 0.4);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .header-gradient {
            background: linear-gradient(90deg, #0066ff 0%, #00ffcc 100%);
            height: 4px;
            box-shadow: 0 0 20px rgba(0, 150, 255, 0.5);
        }

        .cyber-text {
            background: linear-gradient(135deg, #00ffcc 0%, #0066ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(0, 150, 255, 0.5);
        }

        /* Futuristic Logo Styles */
        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .logo-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(0, 150, 255, 0.3) 0%, transparent 70%);
            filter: blur(20px);
            animation: pulseGlow 3s infinite alternate;
        }

        .logo-main {
            position: relative;
            width: 160px;
            height: 160px;
            background: linear-gradient(135deg, rgba(10, 15, 30, 0.9) 0%, rgba(20, 25, 40, 0.9) 100%);
            border-radius: 25px;
            border: 2px solid rgba(0, 150, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 0 60px rgba(0, 150, 255, 0.3),
                inset 0 0 40px rgba(0, 150, 255, 0.1);
            overflow: hidden;
        }

        .logo-inner-glow {
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, transparent 30%, rgba(0, 150, 255, 0.1) 100%);
        }

        .logo-icon {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .logo-heart {
            font-size: 3rem;
            background: linear-gradient(135deg, #00ffcc 0%, #0066ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: heartBeat 2s infinite;
        }

        .logo-text {
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(135deg, #00ffcc 0%, #0066ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
        }

        .logo-ring {
            position: absolute;
            width: 200px;
            height: 200px;
            border: 2px solid rgba(0, 150, 255, 0.3);
            border-radius: 50%;
            animation: rotate 20s linear infinite;
        }

        .logo-ring:nth-child(1) {
            width: 220px;
            height: 220px;
            border-color: rgba(0, 255, 200, 0.2);
            animation-direction: reverse;
            animation-duration: 25s;
        }

        .logo-ring:nth-child(2) {
            width: 240px;
            height: 240px;
            border-color: rgba(0, 150, 255, 0.1);
            animation-duration: 30s;
        }

        .logo-dots {
            position: absolute;
            width: 100%;
            height: 100%;
            animation: rotate 15s linear infinite reverse;
        }

        .logo-dot {
            position: absolute;
            width: 8px;
            height: 8px;
            background: #00ffcc;
            border-radius: 50%;
            box-shadow: 0 0 15px #00ffcc;
        }

        .logo-dot:nth-child(1) {
            top: 10%;
            left: 50%;
        }

        .logo-dot:nth-child(2) {
            top: 50%;
            right: 10%;
        }

        .logo-dot:nth-child(3) {
            bottom: 10%;
            left: 50%;
        }

        .logo-dot:nth-child(4) {
            top: 50%;
            left: 10%;
        }

        @keyframes pulseGlow {
            0% {
                opacity: 0.5;
                transform: translate(-50%, -50%) scale(1);
            }

            100% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        @keyframes heartBeat {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Cyber Lines */
        .cyber-line {
            position: absolute;
            height: 2px;
            background: linear-gradient(90deg, transparent, #00ffcc, transparent);
            animation: scanLine 2s linear infinite;
        }

        .cyber-line:nth-child(1) {
            top: 0;
            left: 0;
            width: 100%;
            animation-delay: 0s;
        }

        .cyber-line:nth-child(2) {
            top: 50%;
            left: 0;
            width: 100%;
            animation-delay: 1s;
        }

        .cyber-line:nth-child(3) {
            bottom: 0;
            left: 0;
            width: 100%;
            animation-delay: 1.5s;
        }

        @keyframes scanLine {
            0% {
                transform: translateX(-100%);
                opacity: 0;
            }

            10%,
            90% {
                opacity: 1;
            }

            100% {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Animated Particles */
        .cyber-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
        }

        .cyber-particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #00ffcc;
            border-radius: 50%;
            box-shadow: 0 0 10px #00ffcc;
            animation: cyberParticleMove 10s linear infinite;
        }

        @keyframes cyberParticleMove {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4 relative">
    <!-- Cyber Background Elements -->
    <div class="cyber-bg"></div>
    <div class="grid-pattern"></div>
    <div class="cyber-glow">
        <div class="cyber-glow-element"></div>
        <div class="cyber-glow-element"></div>
    </div>

    <!-- Cyber Particles -->
    <div class="cyber-particles" id="cyberParticles"></div>

    <div class="relative w-full max-w-lg">
        <!-- Cyber Scan Lines -->
        <div class="absolute -inset-4 overflow-hidden rounded-2xl">
            <div class="cyber-line"></div>
            <div class="cyber-line"></div>
            <div class="cyber-line"></div>
        </div>

        <!-- Decorative header bar -->
        <div class="header-gradient rounded-t-2xl h-1 mb-[-1px]"></div>

        <!-- Main Card -->
        <div class="card-shadow rounded-2xl overflow-hidden">
            <div class="p-8 md:p-10">
                <!-- Futuristic Animated Logo -->
                <div class="text-center mb-8">
                    <div class="logo-container">
                        <div class="logo-glow"></div>
                        <div class="logo-ring"></div>
                        <div class="logo-ring"></div>
                        <div class="logo-ring"></div>
                        <div class="logo-main">
                            <div class="logo-inner-glow"></div>
                            <div class="logo-icon">
                                <i class="fas fa-heartbeat logo-heart"></i>
                                <span class="logo-text">NCHSP</span>
                            </div>
                            <div class="logo-dots">
                                <div class="logo-dot"></div>
                                <div class="logo-dot"></div>
                                <div class="logo-dot"></div>
                                <div class="logo-dot"></div>
                            </div>
                        </div>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-bold cyber-text mt-6 mb-2">
                        National Citizen Health Service Portal
                    </h1>
                    <p class="text-gray-400 font-medium text-sm md:text-base tracking-wide">
                        Next-Generation Health Service Platform
                    </p>
                    <div
                        class="mt-4 inline-flex items-center gap-3 bg-gradient-to-r from-blue-900/30 to-cyan-900/30 text-cyan-300 px-5 py-2.5 rounded-full text-sm font-semibold border border-cyan-500/30">
                        <div class="w-2 h-2 bg-cyan-400 rounded-full animate-pulse"></div>
                        <span>Quantum-Secure Login Portal</span>
                        <i class="fas fa-shield-alt text-cyan-400"></i>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (isset($_GET['registered'])): ?>
                    <div
                        class="bg-gradient-to-r from-emerald-900/20 to-green-900/20 border border-emerald-500/30 text-emerald-300 p-5 rounded-xl mb-6 font-medium animate-fade-in-up backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <i class="fas fa-check-circle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="font-bold text-emerald-200">Registration Successful!</p>
                                <p class="text-sm opacity-90 mt-1">Please login with your credentials.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['doctor_registered'])): ?>
                    <div
                        class="bg-gradient-to-r from-blue-900/20 to-indigo-900/20 border border-blue-500/30 text-blue-300 p-5 rounded-xl mb-6 font-medium animate-fade-in-up backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center shadow-lg shadow-blue-500/20">
                                <i class="fas fa-user-clock text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="font-bold text-blue-200">Registration Submitted!</p>
                                <p class="text-sm opacity-90 mt-1">Your account is pending admin approval.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div
                        class="bg-gradient-to-r from-red-900/20 to-pink-900/20 border border-red-500/30 text-red-300 p-5 rounded-xl mb-6 font-medium animate-fade-in-up backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center shadow-lg shadow-red-500/20">
                                <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="font-bold text-red-200">Access Denied</p>
                                <p class="text-sm opacity-90 mt-1"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="" method="post" class="space-y-7">
                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-bold text-cyan-300 mb-4 tracking-wider">
                            <i class="fas fa-user-tag mr-3 text-cyan-400"></i>
                            SELECT ACCESS TYPE
                        </label>
                        <div class="grid grid-cols-2 gap-5">
                            <label class="role-card cursor-pointer">
                                <input type="radio" name="role" value="citizen" checked class="hidden"
                                    id="citizen-role">
                                <div class="p-5 border rounded-xl text-center" id="citizen-card">
                                    <div
                                        class="relative w-14 h-14 bg-gradient-to-br from-blue-900/50 to-cyan-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-cyan-500/30">
                                        <i class="fas fa-user text-cyan-400 text-xl"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-200">CITIZEN</h3>
                                    <p class="text-xs text-gray-400 mt-1.5 tracking-wide">Public Access</p>
                                </div>
                            </label>
                            <label class="role-card cursor-pointer">
                                <input type="radio" name="role" value="admin" class="hidden" id="admin-role">
                                <div class="p-5 border rounded-xl text-center" id="admin-card">
                                    <div
                                        class="relative w-14 h-14 bg-gradient-to-br from-blue-900/50 to-cyan-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-cyan-500/30">
                                        <i class="fas fa-user-md text-cyan-400 text-xl"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-200">ADMIN/DOCTOR</h3>
                                    <p class="text-xs text-gray-400 mt-1.5 tracking-wide">Medical Staff</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Identifier Input -->
                    <div class="space-y-3">
                        <label class="block text-sm font-bold text-cyan-300 tracking-wider">
                            <i class="fas fa-id-card mr-3 text-cyan-400"></i>
                            <span id="identifier-label">ACCESS IDENTIFIER</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-key text-cyan-400"></i>
                            </div>
                            <input type="text" name="identifier" required
                                class="pl-12 w-full px-5 py-4 border rounded-xl input-focus focus:ring-2 focus:ring-cyan-500/30 focus:border-cyan-500 focus:outline-none placeholder-gray-500"
                                placeholder="Enter your identifier" id="identifier-input">
                        </div>
                        <p class="text-xs text-gray-400 mt-2 tracking-wide" id="identifier-hint">
                            <i class="fas fa-info-circle mr-1.5"></i>
                            Enter your mobile number or Voter ID
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-3">
                        <label class="block text-sm font-bold text-cyan-300 tracking-wider">
                            <i class="fas fa-lock mr-3 text-cyan-400"></i>
                            SECURE PASSWORD
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-cyan-400"></i>
                            </div>
                            <input type="password" name="password" required
                                class="pl-12 w-full px-5 py-4 border rounded-xl input-focus focus:ring-2 focus:ring-cyan-500/30 focus:border-cyan-500 focus:outline-none placeholder-gray-500"
                                placeholder="Enter your secure password">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <button type="button"
                                    class="text-cyan-400 hover:text-cyan-300 transition-colors focus:outline-none"
                                    id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 tracking-wide">
                            <i class="fas fa-shield-alt mr-1.5"></i>
                            Enter your quantum-encrypted password
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login"
                        class="w-full btn-login text-white font-bold py-4.5 rounded-xl transition-all duration-300 mt-3 relative tracking-wider text-lg">
                        <i class="fas fa-sign-in-alt mr-3"></i>
                        LOG IN
                    </button>
                </form>

                <!-- Footer Links -->
                <div class="mt-10 pt-7 border-t border-gray-800/50">
                    <div class="text-center space-y-5">
                        <p class="text-sm text-gray-400 tracking-wide">
                            New to the system?
                            <a href="register.php"
                                class="text-cyan-400 font-bold hover:text-cyan-300 hover:underline ml-2 inline-flex items-center gap-2 transition-all group">
                                CREATE CITIZEN ACCOUNT
                                <i
                                    class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1.5"></i>
                            </a>
                        </p>
                        <div class="pt-4 border-t border-gray-800/50">
                            <p class="text-xs text-gray-600 tracking-wider">
                                <i class="fas fa-copyright mr-1.5"></i>
                                2026 NCHSP ALL RIGHTS RESERVED
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.querySelector('input[name="password"]');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                passwordInput.parentElement.classList.add('border-cyan-400');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                passwordInput.parentElement.classList.remove('border-cyan-400');
            }
        });

        // Role selection visual feedback
        const citizenRole = document.getElementById('citizen-role');
        const adminRole = document.getElementById('admin-role');
        const citizenCard = document.getElementById('citizen-card');
        const adminCard = document.getElementById('admin-card');
        const identifierLabel = document.getElementById('identifier-label');
        const identifierHint = document.getElementById('identifier-hint');
        const identifierInput = document.getElementById('identifier-input');

        function updateRoleSelection() {
            if (citizenRole.checked) {
                citizenCard.classList.add('selected');
                adminCard.classList.remove('selected');
                identifierLabel.textContent = 'ACCESS IDENTIFIER';
                identifierHint.innerHTML = '<i class="fas fa-info-circle mr-1.5"></i> Enter your mobile number or Voter ID';
                identifierInput.placeholder = 'Enter mobile or Voter ID';
            } else {
                adminCard.classList.add('selected');
                citizenCard.classList.remove('selected');
                identifierLabel.textContent = 'ADMIN USERNAME';
                identifierHint.innerHTML = '<i class="fas fa-info-circle mr-1.5"></i> Enter your admin username';
                identifierInput.placeholder = 'Enter admin username';
            }
        }

        citizenRole.addEventListener('change', updateRoleSelection);
        adminRole.addEventListener('change', updateRoleSelection);

        // Initialize on page load
        updateRoleSelection();

        // Add click animation for role cards
        [citizenCard, adminCard].forEach(card => {
            card.addEventListener('click', function () {
                this.style.transform = 'translateY(-4px) scale(1.02)';
                setTimeout(() => {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = '';
                    }
                }, 200);
            });
        });

        // Create cyber particles
        function createCyberParticles() {
            const container = document.getElementById('cyberParticles');
            const particleCount = 40;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('cyber-particle');

                const size = Math.random() * 3 + 1;
                const posX = Math.random() * 100;
                const duration = Math.random() * 15 + 10;
                const delay = Math.random() * 5;
                const opacity = Math.random() * 0.5 + 0.2;
                const color = ['#00ffcc', '#0066ff', '#00ccff'][Math.floor(Math.random() * 3)];

                particle.style.cssText = `
                    left: ${posX}%;
                    width: ${size}px;
                    height: ${size}px;
                    background: ${color};
                    box-shadow: 0 0 ${size * 5}px ${color};
                    animation: cyberParticleMove ${duration}s linear infinite ${delay}s;
                    opacity: ${opacity};
                `;

                container.appendChild(particle);
            }
        }

        // Add input focus effects
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function () {
                this.parentElement.classList.add('border-cyan-400');
            });

            input.addEventListener('blur', function () {
                this.parentElement.classList.remove('border-cyan-400');
            });
        });

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fade-in-up {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .animate-fade-in-up {
                animation: fade-in-up 0.6s ease-out;
            }
            
            /* Button click effect */
            button:active {
                transform: scale(0.98) !important;
            }
            
            /* Smooth transitions */
            * {
                transition: background-color 0.3s, border-color 0.3s, transform 0.3s, box-shadow 0.3s;
            }
        `;
        document.head.appendChild(style);

        // Initialize effects when page loads
        document.addEventListener('DOMContentLoaded', function () {
            createCyberParticles();

            // Add subtle floating animation to main card
            const mainCard = document.querySelector('.card-shadow');
            mainCard.style.animation = 'fade-in-up 0.8s ease-out';

            // Add typing effect to title
            const title = document.querySelector('.cyber-text');
            title.style.animation = 'fade-in-up 1s ease-out';
        });
    </script>
</body>

</html>