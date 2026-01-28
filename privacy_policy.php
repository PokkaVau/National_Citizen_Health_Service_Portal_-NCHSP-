<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Privacy Policy - NCHSP</title>
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
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300"
                    href="about.php">About</a>
                <a class="text-sm font-semibold text-primary transition-all duration-300"
                    href="privacy_policy.php">Privacy Policy</a>
            </nav>
            <div class="flex items-center gap-4">
                <a href="index.php"
                    class="flex items-center gap-2 text-sm font-bold text-gray-600 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back to Home
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section
        class="relative py-16 px-6 bg-gradient-to-b from-white to-gray-50 dark:from-background-dark dark:to-gray-900">
        <div class="max-w-4xl mx-auto text-center">
            <span
                class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider mb-6">Transparency</span>
            <h1 class="text-4xl md:text-5xl font-black mb-6 leading-tight">
                Privacy Policy & <span class="gradient-text">Tech Stack</span>
            </h1>
            <p class="text-lg text-gray-500 leading-relaxed max-w-2xl mx-auto">
                We are committed to protecting your personal health data and being transparent about the technologies
                that power this platform.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-16 px-6 max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- Sidebar Navigation (Sticky) -->
            <div class="hidden lg:block col-span-1">
                <div class="sticky top-24 space-y-2">
                    <a href="#privacy"
                        class="block p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-primary/50 transition-colors font-semibold text-primary">
                        Privacy Policy
                    </a>
                    <a href="#tech-stack"
                        class="block p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-primary/50 transition-colors font-medium hover:text-primary">
                        Technology Stack
                    </a>
                    <a href="#contact"
                        class="block p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-primary/50 transition-colors font-medium hover:text-primary">
                        Contact Us
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-span-1 lg:col-span-2 space-y-16">

                <!-- Privacy Section -->
                <div id="privacy" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 rounded-xl bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-2xl">security</span>
                        </div>
                        <h2 class="text-2xl font-bold">Privacy Policy</h2>
                    </div>

                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 space-y-6">
                        <p>
                            At the National Citizen Health Service Portal (NCHSP), we take your privacy seriously. This
                            policy outlines how we collect, use, and protect your personal and medical information.
                        </p>

                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-8">1. Information We Collect</h3>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong>Personal Identification:</strong> Name, National ID, Date of Birth, Address.
                            </li>
                            <li><strong>Contact Information:</strong> Phone number, Email address.</li>
                            <li><strong>Health Data:</strong> Medical history, prescriptions, lab reports, blood group,
                                weight, and height.</li>
                            <li><strong>Log Data:</strong> IP address, browser type, and access times for security
                                auditing.</li>
                        </ul>

                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-8">2. How We Use Your Data</h3>
                        <p>Your data is used solely for:</p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Providing digital health records and efficient medical services.</li>
                            <li>Facilitating appointments with doctors and health camps.</li>
                            <li>Sending critical health alerts and report notifications.</li>
                            <li>Improving public health strategies through anonymized analytics.</li>
                        </ul>

                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-8">3. Data Security</h3>
                        <p>
                            We employ state-of-the-art security measures including <strong>AES-256 encryption</strong>
                            for data at rest and <strong>TLS 1.3</strong> for data in transit. Access to sensitive
                            health records is strictly controlled via role-based authentication.
                        </p>
                    </div>
                </div>

                <!-- Tech Stack Section -->
                <div id="tech-stack" class="scroll-mt-24 pt-8 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 rounded-xl bg-secondary/10 text-secondary">
                            <span class="material-symbols-outlined text-2xl">code</span>
                        </div>
                        <h2 class="text-2xl font-bold">Technology Stack</h2>
                    </div>

                    <p class="text-gray-600 dark:text-gray-300 mb-8">
                        The NCHSP platform is built using robust, modern technologies to ensure performance, security,
                        and scalability.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                            <h4 class="font-bold text-lg mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-orange-500">html</span> Frontend
                            </h4>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-center gap-2">
                                    <span class="size-1.5 rounded-full bg-orange-500"></span>
                                    <strong>HTML5:</strong> Semantic structure
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="size-1.5 rounded-full bg-blue-400"></span>
                                    <strong>Tailwind CSS:</strong> Utility-first styling
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="size-1.5 rounded-full bg-yellow-400"></span>
                                    <strong>Vanilla JavaScript:</strong> Lightweight interactivity
                                </li>
                            </ul>
                        </div>

                        <div
                            class="p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                            <h4 class="font-bold text-lg mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-500">dns</span> Backend & Database
                            </h4>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-center gap-2">
                                    <span class="size-1.5 rounded-full bg-indigo-500"></span>
                                    <strong>PHP:</strong> Server-side logic
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="size-1.5 rounded-full bg-blue-600"></span>
                                    <strong>MySQL:</strong> Relational database management
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="size-1.5 rounded-full bg-green-500"></span>
                                    <strong>Apache:</strong> Web server
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Contact Section -->
                <div id="contact" class="scroll-mt-24 pt-8 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-2xl">mail</span>
                        </div>
                        <h2 class="text-2xl font-bold">Questions?</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300">
                        If you have any questions about this Privacy Policy or our technology practices, please contact
                        our Data Protection Officer at <a href="mailto:privacy@nchsp.gov.bd"
                            class="text-primary font-bold hover:underline">privacy@nchsp.gov.bd</a>.
                    </p>
                </div>
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