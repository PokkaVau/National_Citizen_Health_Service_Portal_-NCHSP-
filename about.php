<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>About Us - NCHSP</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#117b8d",
                        "secondary": "#3D9970",
                        "background-light": "#f7f7f8",
                        "background-dark": "#1c1f22",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Public Sans', sans-serif;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(17, 123, 141, 0.1);
        }

        .dark .glass-header {
            background: rgba(28, 31, 34, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #117b8d 0%, #3D9970 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111718] dark:text-white transition-colors duration-300">
    <!-- Header -->
    <header class="sticky top-0 z-50 glass-header shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3 group cursor-pointer">
                <div
                    class="size-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl">health_and_safety</span>
                </div>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-primary uppercase">NCHSP</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">National Portal</p>
                </div>
            </a>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300"
                    href="index.php">Home</a>
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300"
                    href="services.php">Services</a>
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300"
                    href="index.php#camps">Camps</a>
                <a class="text-sm font-semibold text-primary transition-all duration-300" href="about.php">About</a>
            </nav>
            <div class="flex items-center gap-4">
                <a href="login.php"
                    class="bg-gradient-to-r from-primary to-secondary hover:from-primary/90 hover:to-secondary/90 text-white px-6 py-3 rounded-xl text-sm font-bold shadow-lg transition-all duration-300">
                    Login / Register
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section
        class="relative py-24 px-6 bg-gradient-to-b from-white to-gray-50 dark:from-background-dark dark:to-gray-900">
        <div class="max-w-4xl mx-auto text-center">
            <span
                class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider mb-6">Who
                We Are</span>
            <h1 class="text-5xl md:text-6xl font-black mb-8 leading-tight">
                About <span class="gradient-text">NCHSP</span>
            </h1>
            <p class="text-xl text-gray-500 leading-relaxed">
                The National Citizen Health Service Portal (NCHSP) is a government-supported digital healthcare
                initiative designed to provide free, secure, and accessible health services to every citizen. Our
                mission is to modernize public healthcare by transforming traditional, paper-based medical records into
                a centralized digital health system.
            </p>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-20 px-6 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Mission -->
            <div
                class="bg-white dark:bg-gray-800 p-10 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="size-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-8">
                    <span class="material-symbols-outlined text-4xl text-primary">flag</span>
                </div>
                <h3 class="text-3xl font-bold mb-6">Our Mission</h3>
                <p class="text-gray-500 mb-6">To ensure equal healthcare access for all citizens by leveraging digital
                    technology to:</p>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-green-500">check_circle</span>
                        <span>Simplify health record management</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-green-500">check_circle</span>
                        <span>Improve transparency in medical reporting</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-green-500">check_circle</span>
                        <span>Encourage preventive healthcare</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-green-500">check_circle</span>
                        <span>Reduce manual paperwork and delays</span>
                    </li>
                </ul>
            </div>

            <!-- Vision -->
            <div
                class="bg-white dark:bg-gray-800 p-10 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="size-16 bg-secondary/10 rounded-2xl flex items-center justify-center mb-8">
                    <span class="material-symbols-outlined text-4xl text-secondary">visibility</span>
                </div>
                <h3 class="text-3xl font-bold mb-6">Our Vision</h3>
                <p class="text-gray-500 mb-6">We envision a future where:</p>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-500">verified</span>
                        <span>Every citizen has a secure digital health identity</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-500">verified</span>
                        <span>Medical data is easily accessible anytime, anywhere</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-500">verified</span>
                        <span>Preventive healthcare becomes a national priority</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-500">verified</span>
                        <span>Technology strengthens the public healthcare system</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- What We Offer -->
    <section class="py-20 px-6 bg-gray-50 dark:bg-gray-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black mb-4">What We Offer</h2>
                <div class="h-1.5 w-24 bg-gradient-to-r from-primary to-secondary rounded-full mx-auto"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-4xl text-primary mb-4">monitor_heart</span>
                    <h4 class="text-xl font-bold mb-2">Digital Health Reports</h4>
                    <p class="text-gray-500 text-sm">Access blood sugar, cholesterol, SGPT, and other test results
                        online.</p>
                </div>
                <!-- Service 2 -->
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-4xl text-secondary mb-4">analytics</span>
                    <h4 class="text-xl font-bold mb-2">Health History & Analytics</h4>
                    <p class="text-gray-500 text-sm">Track yearly progress through graphs and health insights.</p>
                </div>
                <!-- Service 3 -->
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-4xl text-orange-500 mb-4">apartment</span>
                    <h4 class="text-xl font-bold mb-2">Hospital Integration</h4>
                    <p class="text-gray-500 text-sm">Seamless coordination between hospitals, health camps, and
                        citizens.</p>
                </div>
                <!-- Service 4 -->
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-4xl text-purple-500 mb-4">notifications_active</span>
                    <h4 class="text-xl font-bold mb-2">Smart Notifications</h4>
                    <p class="text-gray-500 text-sm">Receive SMS and email alerts for reports, camps, and reminders.</p>
                </div>
                <!-- Service 5 -->
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-4xl text-pink-500 mb-4">lock_reset</span>
                    <h4 class="text-xl font-bold mb-2">Secure PDF Downloads</h4>
                    <p class="text-gray-500 text-sm">Download verified medical reports anytime.</p>
                </div>
                <!-- Service 6 -->
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <span class="material-symbols-outlined text-4xl text-blue-500 mb-4">encrypted</span>
                    <h4 class="text-xl font-bold mb-2">Data Security</h4>
                    <p class="text-gray-500 text-sm">We prioritize the security and privacy of citizen health data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Security & Commitment -->
    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div
            class="bg-gradient-to-br from-primary to-secondary rounded-3xl p-12 md:p-20 text-white text-center relative overflow-hidden">
            <div class="relative z-10 max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-5xl font-black mb-8">Our Commitment</h2>
                <p class="text-lg opacity-90 mb-10 leading-relaxed">
                    NCHSP is committed to supporting the national vision of Digital Healthcare for All. We believe that
                    a healthy nation begins with informed citizens. NCHSP stands as a bridge between people, healthcare
                    providers, and technology — working together for a healthier tomorrow.
                </p>
                <div class="flex flex-wrap justify-center gap-4 text-sm font-semibold opacity-80">
                    <span class="bg-white/20 px-4 py-2 rounded-full">Encrypted Storage</span>
                    <span class="bg-white/20 px-4 py-2 rounded-full">Secure Auth</span>
                    <span class="bg-white/20 px-4 py-2 rounded-full">OTP Verification</span>
                    <span class="bg-white/20 px-4 py-2 rounded-full">Role-Based Access</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 py-12">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-2">
                <div class="size-8 bg-primary rounded-lg flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-sm">health_and_safety</span>
                </div>
                <span class="font-bold text-gray-900 dark:text-white">NCHSP</span>
            </div>
            <p class="text-gray-500 text-sm">© 2026 National Citizen Health Service Portal. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>