<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Terms of Service - NCHSP</title>
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
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300"
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
                class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider mb-6">Legal</span>
            <h1 class="text-4xl md:text-5xl font-black mb-6 leading-tight">
                Terms of <span class="gradient-text">Service</span>
            </h1>
            <p class="text-lg text-gray-500 leading-relaxed max-w-2xl mx-auto">
                Please read these terms and conditions carefully before using the National Citizen Health Service
                Portal.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-16 px-6 max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- Sidebar Navigation (Sticky) -->
            <div class="hidden lg:block col-span-1">
                <div class="sticky top-24 space-y-2">
                    <a href="#acceptance"
                        class="block p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-primary/50 transition-colors font-semibold text-primary">
                        Acceptance of Terms
                    </a>
                    <a href="#usage"
                        class="block p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-primary/50 transition-colors font-medium hover:text-primary">
                        Usage & Conduct
                    </a>
                    <a href="#disclaimer"
                        class="block p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-primary/50 transition-colors font-medium hover:text-primary">
                        Medical Disclaimer
                    </a>
                    <a href="#contact"
                        class="block p-3 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-primary/50 transition-colors font-medium hover:text-primary">
                        Contact Us
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-span-1 lg:col-span-2 space-y-16">

                <!-- Acceptance Section -->
                <div id="acceptance" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 rounded-xl bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-2xl">gavel</span>
                        </div>
                        <h2 class="text-2xl font-bold">1. Acceptance of Terms</h2>
                    </div>

                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 space-y-6">
                        <p>
                            By accessing and using the National Citizen Health Service Portal (NCHSP), you accept and
                            agree to be bound by the terms and provision of this agreement. In addition, when using this
                            portal's particular services, you shall be subject to any posted guidelines or rules
                            applicable to such services.
                        </p>
                        <p>
                            Any participation in this service will constitute acceptance of this agreement. If you do
                            not agree to abide by the above, please do not use this service.
                        </p>
                    </div>
                </div>

                <!-- Usage Section -->
                <div id="usage" class="scroll-mt-24 pt-8 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 rounded-xl bg-secondary/10 text-secondary">
                            <span class="material-symbols-outlined text-2xl">verified_user</span>
                        </div>
                        <h2 class="text-2xl font-bold">2. Usage & Conduct</h2>
                    </div>

                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 space-y-6">
                        <p>
                            You agree to use the portal only for lawful purposes and in accordance with these Terms. You
                            agree not to use the portal:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>In any way that violates any applicable federal, state, local, or international law or
                                regulation.</li>
                            <li>To impersonate or attempt to impersonate the NCHSP, an NCHSP employee, another user, or
                                any other person or entity.</li>
                            <li>To engage in any other conduct that restricts or inhibits anyone's use or enjoyment of
                                the portal, or which, as determined by us, may harm NCHSP or users of the portal or
                                expose them to liability.</li>
                        </ul>
                    </div>
                </div>

                <!-- Disclaimer Section -->
                <div id="disclaimer" class="scroll-mt-24 pt-8 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 rounded-xl bg-orange-500/10 text-orange-500">
                            <span class="material-symbols-outlined text-2xl">warning</span>
                        </div>
                        <h2 class="text-2xl font-bold">3. Medical Disclaimer</h2>
                    </div>

                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 space-y-6">
                        <p>
                            The contents of the NCHSP, such as text, graphics, images, and other material contained on
                            the portal ("Content") are for informational purposes only. The Content is not intended to
                            be a substitute for professional medical advice, diagnosis, or treatment.
                        </p>
                        <p>
                            <strong class="text-gray-900 dark:text-white">Always seek the advice of your physician or
                                other qualified health provider with any questions you may have regarding a medical
                                condition.</strong> Never disregard professional medical advice or delay in seeking it
                            because of something you have read on the NCHSP.
                        </p>
                        <p>
                            If you think you may have a medical emergency, call your doctor or emergency services
                            immediately. NCHSP does not recommend or endorse any specific tests, physicians, products,
                            procedures, opinions, or other information that may be mentioned on the Site.
                        </p>
                    </div>
                </div>

                <!-- Contact Section -->
                <div id="contact" class="scroll-mt-24 pt-8 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-2xl">mail</span>
                        </div>
                        <h2 class="text-2xl font-bold">Contact Us</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300">
                        If you have any questions about these Terms, please contact us at <a
                            href="mailto:legal@nchsp.gov.bd"
                            class="text-primary font-bold hover:underline">legal@nchsp.gov.bd</a>.
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