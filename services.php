<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Our Services - NCHSP</title>
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

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(17, 123, 141, 0.15);
        }

        .dark .hover-lift:hover {
            box-shadow: 0 12px 24px rgba(17, 123, 141, 0.25);
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
                <a class="text-sm font-semibold text-primary transition-all duration-300"
                    href="services.php">Services</a>
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300"
                    href="index.php#camps">Camps</a>
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300"
                    href="about.php">About</a>
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
        class="relative py-20 px-6 bg-gradient-to-b from-white to-gray-50 dark:from-background-dark dark:to-gray-900">
        <div class="max-w-4xl mx-auto text-center">
            <span
                class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider mb-6">What
                We Do</span>
            <h1 class="text-4xl md:text-6xl font-black mb-8 leading-tight">
                Comprehensive <span class="gradient-text">Health Services</span>
            </h1>
            <p class="text-xl text-gray-500 leading-relaxed max-w-2xl mx-auto">
                Discover the range of digital and physical healthcare services available to every citizen through the
                National Citizen Health Service Portal.
            </p>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="py-20 px-6 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <!-- Service 1 -->
            <div
                class="group p-8 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 hover-lift relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[8rem]">folder_managed</span>
                </div>
                <div
                    class="size-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-3xl">folder_managed</span>
                </div>
                <h3 class="text-2xl font-bold mb-4">Digital Health Records</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Securely store and access your complete medical history, including prescriptions, diagnosis reports,
                    and vaccination records. Eliminate the need for physical files.
                </p>
                <ul class="space-y-2 text-sm text-gray-500 font-medium">
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-blue-500"></span>24/7
                        Access</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-blue-500"></span>Doctor
                        Sharing</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-blue-500"></span>Encrypted
                        Storage</li>
                </ul>
            </div>

            <!-- Service 2 -->
            <div
                class="group p-8 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 hover-lift relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[8rem]">medical_services</span>
                </div>
                <div
                    class="size-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-3xl">medical_services</span>
                </div>
                <h3 class="text-2xl font-bold mb-4">Free Checkups & Camps</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Locate government-sponsored health camps near you. Get free general checkups, eye exams, and
                    specialist consultations without any cost.
                </p>
                <ul class="space-y-2 text-sm text-gray-500 font-medium">
                    <li class="flex items-center gap-2"><span
                            class="size-1.5 rounded-full bg-emerald-500"></span>Location-based Search</li>
                    <li class="flex items-center gap-2"><span
                            class="size-1.5 rounded-full bg-emerald-500"></span>Instant Booking</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-emerald-500"></span>Event
                        Reminders</li>
                </ul>
            </div>

            <!-- Service 3 -->
            <div
                class="group p-8 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 hover-lift relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[8rem]">video_chat</span>
                </div>
                <div
                    class="size-14 rounded-2xl bg-orange-50 dark:bg-orange-900/20 text-orange-600 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-3xl">video_chat</span>
                </div>
                <h3 class="text-2xl font-bold mb-4">Tele-Health Consultations</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Connect with certified doctors from the comfort of your home. Schedule video consultations for
                    non-emergency medical advice and follow-ups.
                </p>
                <ul class="space-y-2 text-sm text-gray-500 font-medium">
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-orange-500"></span>HD
                        Video Calls</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-orange-500"></span>Digital
                        Prescriptions</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-orange-500"></span>Secure
                        Private Chat</li>
                </ul>
            </div>

            <!-- Service 4 -->
            <div
                class="group p-8 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 hover-lift relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[8rem]">labs</span>
                </div>
                <div
                    class="size-14 rounded-2xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-3xl">labs</span>
                </div>
                <h3 class="text-2xl font-bold mb-4">Lab Reports Integration</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    Get your test results delivered directly to your portal account from partnered diagnostic centers.
                    View trends and analytics on your vital stats.
                </p>
                <ul class="space-y-2 text-sm text-gray-500 font-medium">
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-purple-500"></span>Visual
                        Graphs</li>
                    <li class="flex items-center gap-2"><span
                            class="size-1.5 rounded-full bg-purple-500"></span>Downloadable PDFs</li>
                    <li class="flex items-center gap-2"><span
                            class="size-1.5 rounded-full bg-purple-500"></span>Historical Data</li>
                </ul>
            </div>

            <!-- Service 5 -->
            <div
                class="group p-8 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 hover-lift relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[8rem]">apartment</span>
                </div>
                <div
                    class="size-14 rounded-2xl bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-3xl">apartment</span>
                </div>
                <h3 class="text-2xl font-bold mb-4">Hospital Network</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                    A unified network of public and private hospitals. Check bed availability, department schedules, and
                    specialists' consulting hours in real-time.
                </p>
                <ul class="space-y-2 text-sm text-gray-500 font-medium">
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-cyan-500"></span>Live Data
                    </li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-cyan-500"></span>Resource
                        Management</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-cyan-500"></span>Emergency
                        Contacts</li>
                </ul>
            </div>

            <!-- Service 6 -->
            <div
                class="group p-8 bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-300 dark:border-gray-600 hover:border-primary transition-colors flex flex-col items-center justify-center text-center">
                <div class="size-16 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl text-gray-400">add</span>
                </div>
                <h3 class="text-xl font-bold mb-2">More Coming Soon</h3>
                <p class="text-gray-500 text-sm">We are constantly expanding our services to serve you better.</p>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 py-12 mt-12">
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