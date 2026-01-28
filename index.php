<?php
require 'config/db.php';

// Fetch Camps
$stmt = $pdo->query("SELECT * FROM health_camps ORDER BY camp_date ASC"); // ASC for upcoming usually, but let's stick to logic
$camps = $stmt->fetchAll();

// Helper Functions
function getCampStatus($camp_date)
{
    $today = new DateTime();
    $campDate = new DateTime($camp_date);
    $today->setTime(0, 0, 0);
    $campDate->setTime(0, 0, 0);

    if ($today > $campDate) {
        return ['status' => 'Completed', 'class' => 'bg-gray-100 text-gray-500'];
    } elseif ($today == $campDate) {
        return ['status' => 'Live Now', 'class' => 'bg-gradient-to-r from-secondary to-emerald-500 text-white animate-pulse'];
    } elseif ($campDate->diff($today)->days <= 7) {
        return ['status' => 'Upcoming', 'class' => 'bg-blue-50 text-blue-600'];
    } else {
        return ['status' => 'Scheduled', 'class' => 'bg-purple-50 text-purple-600'];
    }
}

function getTimeRemaining($camp_date)
{
    $today = new DateTime();
    $campDate = new DateTime($camp_date);
    $today->setTime(0, 0, 0);
    $campDate->setTime(0, 0, 0);

    if ($today > $campDate) {
        return 'Completed';
    }

    $interval = $today->diff($campDate);
    if ($interval->days == 0) {
        return 'Today';
    }
    if ($interval->days == 1) {
        return 'Tomorrow';
    }
    return $interval->days . ' days left';
}

// Image placeholders for rotation
$campImages = [
    "https://lh3.googleusercontent.com/aida-public/AB6AXuDT5yQ8F9MCvXTJMDAgJwHq-cpSfvD3N_LGoC0qeikzd0CgFr46brdtsNOYEqtlWxf6zHaA99w17zkG2BY0TYZ6Br0wR6Mh6lWTYI0VrSXwFiAa51_HoD_e0MIL7gvC9QIPG1tOcUcvZ2OUhISCbmc-EVQyGGG7wDO6P5V1iUhxu6uZRLfdaYfCaNuK8xvobPtkF8wr3rBtb4bDzC8ECrxxXjrZWGD0AZIco08i9X2gX0-lxzPIpt74KMJZskzaKLuuZz2OvfEMczfK",
    "https://lh3.googleusercontent.com/aida-public/AB6AXuB5sRHZf9IvKI-3Kz0Tfno52ukg5DkX7J_6WljMRSkdReWTpSsWtaxpaNHJ6FO0VYe0o2a9ziYKRslaBQ1VUfFJW5zL694cmkU9PgCU4IYUPmJOJ2mE-I8Fm1_r7EUhxf_uTQtIFIOGe_pa86AKtIYxnIft6E5Abejlb_MKn-oF5Yyp4kRjl8i1NY0cOrcuExOmVIqHn7YgwdOYSA4rRxuhfliv3y4jUYR3SgcbPXTx1sq61x27mwjVwOtpJ4VzvaPBwH8iNnSjauyM",
    "https://lh3.googleusercontent.com/aida-public/AB6AXuA_BA9e3KllrqaMNgaEwIYkaNvIHMGWeTwTXztsC4FiCDw5oIYr5C7Qdn2HtldndviuLaV4bOOPuWc3OOhsjv0WJ9AJYmZiEzo7DL9rxSLuKMnQfPq5CcrE_Ca6wgbce0TbezfjZo0hMqtc9uHxmr1sxdmRhX96enDaBcBx0G8QpDXKvljaw8D2mgdx5ImWMEbuP4jZT2iWxbMrBWQZnPFTcg8VHxIk76rTqGRMBrbKQwkbVI2ltTkulMbyxgEWwcvoBJHmk53rE073",
    "https://lh3.googleusercontent.com/aida-public/AB6AXuAeeeJGhCi4H1yig4ptdJ9yoS43SqOLqwPiF0opTiMNwy7nASRSMC6ifRiwwSChAGKgdi3XubJ4_dqylT4ivQ4XGI84sUN9TNEJvNy9DQ5OFoCmliNoghvmKdRQXQoHOFK2aq8w4x0eLFjVDK_T_Ol9Ig1lreBvGYHCWGvMERBdbC4CfhQywXm6niaCR7wcIgzB9M4yeS_RkddAEazxSQvyn9OddaOR8eofDto7ihwbrvY-w0wdhkuE3RCOmiO5mfzaHV7LYQ8122fm"
];
?>
<!DOCTYPE html>

<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>NCHSP - Modern Health Portal</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
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
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                        'pulse-glow': 'pulseGlow 2s infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        pulseGlow: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.8', boxShadow: '0 0 20px rgba(17, 123, 141, 0.3)' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
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

        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(17, 123, 141, 0.1);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #117b8d, #3D9970);
            border-radius: 10px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #117b8d 0%, #3D9970 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-border {
            position: relative;
            border: double 2px transparent;
            border-radius: 1rem;
            background-image: linear-gradient(white, white),
                linear-gradient(135deg, #117b8d 0%, #3D9970 100%);
            background-origin: border-box;
            background-clip: padding-box, border-box;
        }

        .dark .gradient-border {
            background-image: linear-gradient(#1c1f22, #1c1f22),
                linear-gradient(135deg, #117b8d 0%, #3D9970 100%);
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

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .dark .stat-card::before {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111718] dark:text-white transition-colors duration-300">
    <!-- Header Section - Enhanced -->
    <header class="sticky top-0 z-50 glass-header shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3 group cursor-pointer">
                <div
                    class="size-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl animate-pulse-glow">health_and_safety</span>
                </div>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-primary uppercase">NCHSP</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">National Portal</p>
                </div>
            </div>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300 relative group"
                    href="services.php">
                    Services
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-primary group-hover:w-full transition-all duration-300"></span>
                </a>
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300 relative group"
                    href="#camps">
                    Camps
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                </a>
                <a class="text-sm font-semibold hover:text-primary transition-all duration-300 relative group"
                    href="about.php">
                    About
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                </a>
            </nav>
            <div class="flex items-center gap-4">
                <button
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900 hover:from-gray-200 dark:hover:from-gray-700 transition-all duration-300 shadow-sm hover:shadow">
                    <span class="material-symbols-outlined text-[20px] text-primary">language</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">EN</span>
                </button>
                <a href="login.php"
                    class="bg-gradient-to-r from-primary to-secondary hover:from-primary/90 hover:to-secondary/90 text-white px-6 py-3 rounded-xl text-sm font-bold shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/35 transition-all duration-300 active:scale-[0.98] animate-slide-up">
                    Login / Register
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section - Enhanced -->
    <section class="relative w-full min-h-[720px] flex items-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-r from-[#111718]/95 via-[#111718]/60 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#111718] via-transparent to-transparent z-10"></div>
            <div class="w-full h-full bg-center bg-cover scale-105 hover:scale-100 transition-transform duration-700"
                data-alt="Healthcare professional providing digital checkup to a citizen"
                style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuB5Lm6K7umQL5BI7-0QAtdlfs5kVOOKY2_wRd1xUfojPl8uM5KJ6RZiRNaHKyBYwUqx_iHkCluaeb_T4pKx9cgLHv3NBXhRoHgn_nSIXX0wR6Z4tbcTkuePvSU7QXR4E4BVb8JoQKpNqtK6f5tXdklMGxjVFeXP9mRNwgsAm9yJP7-GkziXhtUVGJTVSWPJfiEE6ZiEECjOUGY5Qvj6JAN1jNFE1Vl82-pen9yYpvKiCYUCs-QmjjpJlDATZOI11QAGNRL7i6HL7WCv");'>
            </div>
        </div>
        <div class="relative z-20 max-w-7xl mx-auto px-6 w-full">
            <div class="max-w-2xl animate-fade-in">
                <span
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-primary/20 to-secondary/20 text-white border border-primary/30 text-xs font-bold uppercase tracking-[0.2em] mb-6 animate-slide-up">
                    <span class="pulse-dot size-2 bg-secondary rounded-full"></span>
                    Universal Health Access
                </span>
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white leading-[1.05] mb-6">
                    Your Health, <br />
                    <span class="gradient-text">Digitized</span> and Secured.
                </h1>
                <p class="text-xl text-gray-200 leading-relaxed mb-10 max-w-xl">
                    Access free checkups, manage medical records, and connect with healthcare providers across the
                    nation through one secure platform.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="register.php"
                        class="group bg-gradient-to-r from-primary to-secondary text-white px-8 py-4 rounded-xl text-lg font-bold hover:from-primary/90 hover:to-secondary/90 transition-all duration-300 flex items-center justify-center gap-2 shadow-2xl shadow-primary/30 hover:shadow-3xl hover:shadow-primary/40 animate-pulse-glow">
                        Get Started Now
                        <span
                            class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                    <button
                        class="bg-white/15 backdrop-blur-lg text-white border-2 border-white/30 px-8 py-4 rounded-xl text-lg font-bold hover:bg-white/25 transition-all duration-300 hover:border-white/40">
                        How it works
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Services - Enhanced -->
    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="flex items-end justify-between mb-16">
            <div>
                <h2 class="text-4xl font-black tracking-tight mb-3">Key Services</h2>
                <div class="h-1.5 w-24 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
            </div>
            <p class="text-gray-500 max-w-md text-right hidden md:block">
                Streamlining healthcare for every citizen through integrated digital solutions and community-focused
                services.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
                class="group p-8 bg-white dark:bg-gray-800/50 rounded-2xl gradient-border hover-lift transition-all duration-500 animate-slide-up">
                <div
                    class="size-16 rounded-2xl bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center text-primary mb-6 group-hover:scale-110 transition-transform duration-500 shadow-lg">
                    <span class="material-symbols-outlined text-3xl">folder_managed</span>
                </div>
                <h3 class="text-xl font-bold mb-3 group-hover:text-primary transition-colors">Digital Records</h3>
                <p class="text-gray-500 dark:text-gray-400 leading-relaxed mb-6">All your medical history,
                    prescriptions, and
                    lab reports stored in one secure, encrypted place accessible only by you.</p>
                <span
                    class="inline-flex items-center gap-1 text-primary text-sm font-semibold group-hover:gap-2 transition-all">
                    Learn more
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </span>
            </div>
            <div
                class="group p-8 bg-white dark:bg-gray-800/50 rounded-2xl gradient-border hover-lift transition-all duration-500 animate-slide-up animation-delay-100">
                <div
                    class="size-16 rounded-2xl bg-gradient-to-br from-secondary/10 to-secondary/5 flex items-center justify-center text-secondary mb-6 group-hover:scale-110 transition-transform duration-500 shadow-lg">
                    <span class="material-symbols-outlined text-3xl">medical_services</span>
                </div>
                <h3 class="text-xl font-bold mb-3 group-hover:text-secondary transition-colors">Free Checkups</h3>
                <p class="text-gray-500 dark:text-gray-400 leading-relaxed mb-6">Locate and book appointments at free
                    national health camps organized by certified healthcare providers in your district.</p>
                <span
                    class="inline-flex items-center gap-1 text-secondary text-sm font-semibold group-hover:gap-2 transition-all">
                    Find camps
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </span>
            </div>
            <div
                class="group p-8 bg-white dark:bg-gray-800/50 rounded-2xl gradient-border hover-lift transition-all duration-500 animate-slide-up animation-delay-200">
                <div
                    class="size-16 rounded-2xl bg-gradient-to-br from-orange-500/10 to-orange-500/5 flex items-center justify-center text-orange-500 mb-6 group-hover:scale-110 transition-transform duration-500 shadow-lg">
                    <span class="material-symbols-outlined text-3xl">video_chat</span>
                </div>
                <h3 class="text-xl font-bold mb-3 group-hover:text-orange-500 transition-colors">Tele-Health</h3>
                <p class="text-gray-500 dark:text-gray-400 leading-relaxed mb-6">Connect with specialized doctors via
                    secure
                    video calls from the comfort of your home, reducing travel time and costs.</p>
                <span
                    class="inline-flex items-center gap-1 text-orange-500 text-sm font-semibold group-hover:gap-2 transition-all">
                    Schedule now
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </span>
            </div>
        </div>
    </section>

    <!-- Impact Section (Why NCHSP) - Enhanced -->
    <section class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-background-dark py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-20">
                <h2 class="text-4xl md:text-5xl font-black mb-4 gradient-text">Driving National Impact</h2>
                <p class="text-gray-500 text-lg">Modernizing the healthcare landscape for a healthier tomorrow.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="stat-card p-8 bg-white dark:bg-gray-800 rounded-3xl shadow-sm hover-lift">
                    <div
                        class="size-20 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-full flex items-center justify-center shadow-lg mb-6 mx-auto animate-float">
                        <span class="material-symbols-outlined text-4xl text-primary">eco</span>
                    </div>
                    <h4 class="text-2xl font-bold mb-3 text-center">Reduced Paper</h4>
                    <p class="text-gray-500 text-center mb-6">100% paperless record management system saving environment
                        and time.</p>
                    <div class="text-center">
                        <span class="inline-block text-3xl font-black gradient-text">98%</span>
                        <p class="text-sm text-gray-400">Less paper usage</p>
                    </div>
                </div>
                <div class="stat-card p-8 bg-white dark:bg-gray-800 rounded-3xl shadow-sm hover-lift">
                    <div class="size-20 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-full flex items-center justify-center shadow-lg mb-6 mx-auto animate-float"
                        style="animation-delay: 0.2s">
                        <span class="material-symbols-outlined text-4xl text-primary">diversity_3</span>
                    </div>
                    <h4 class="text-2xl font-bold mb-3 text-center">Equal Access</h4>
                    <p class="text-gray-500 text-center mb-6">Ensuring every citizen, regardless of location, gets
                        quality healthcare.</p>
                    <div class="text-center">
                        <span class="inline-block text-3xl font-black gradient-text">50M+</span>
                        <p class="text-sm text-gray-400">Citizens served</p>
                    </div>
                </div>
                <div class="stat-card p-8 bg-white dark:bg-gray-800 rounded-3xl shadow-sm hover-lift">
                    <div class="size-20 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-full flex items-center justify-center shadow-lg mb-6 mx-auto animate-float"
                        style="animation-delay: 0.4s">
                        <span class="material-symbols-outlined text-4xl text-primary">security</span>
                    </div>
                    <h4 class="text-2xl font-bold mb-3 text-center">Secure Database</h4>
                    <p class="text-gray-500 text-center mb-6">Military-grade encryption protecting your private medical
                        information.</p>
                    <div class="text-center">
                        <span class="inline-block text-3xl font-black gradient-text">99.9%</span>
                        <p class="text-sm text-gray-400">Uptime & Security</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Health Camp Highlights (Carousel style) - Enhanced -->
    <section id="camps" class="py-24 px-6 max-w-7xl mx-auto overflow-hidden">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl font-black tracking-tight mb-2">Upcoming Health Camps</h2>
                <div class="h-1.5 w-16 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
            </div>
            <button class="group text-primary font-bold flex items-center gap-1 hover:gap-2 transition-all">
                View All Camps
                <span
                    class="material-symbols-outlined group-hover:translate-x-1 transition-transform">chevron_right</span>
            </button>
        </div>
        <div class="flex gap-6 overflow-x-auto pb-10 custom-scrollbar">
            <?php if (empty($camps)): ?>
                <div class="w-full text-center py-10 text-gray-500">
                    <p>No health camps scheduled at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($camps as $index => $camp):
                    $status = getCampStatus($camp['camp_date']);
                    $timeRemaining = getTimeRemaining($camp['camp_date']);

                    if (!empty($camp['image_path']) && file_exists($camp['image_path'])) {
                        $image = htmlspecialchars($camp['image_path']);
                    } else {
                        $image = $campImages[$index % count($campImages)];
                    }
                    ?>
                    <!-- Camp Card - Dynamic -->
                    <div
                        class="min-w-[340px] bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex-shrink-0 hover-lift group">
                        <div
                            class="h-48 rounded-xl bg-gradient-to-br from-primary/20 to-secondary/20 mb-6 overflow-hidden relative">
                            <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($camp['name']); ?>" />
                            <div
                                class="absolute top-4 left-4 <?php echo $status['class']; ?> text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                <?php echo $status['status']; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-primary text-sm">calendar_today</span>
                            <span class="text-xs font-bold text-gray-500 uppercase">
                                <?php echo date('M d, Y', strtotime($camp['camp_date'])); ?>
                            </span>
                            <span class="ml-auto text-xs text-gray-400 font-medium">
                                <?php echo $timeRemaining; ?>
                            </span>
                        </div>
                        <h4
                            class="font-bold text-xl mb-2 leading-tight group-hover:text-primary transition-colors line-clamp-1">
                            <?php echo htmlspecialchars($camp['name']); ?>
                        </h4>
                        <p class="text-gray-500 text-sm mb-6 line-clamp-2">
                            <?php echo htmlspecialchars($camp['location']); ?>
                        </p>
                        <?php if ($camp['google_map_link']): ?>
                            <a href="<?php echo htmlspecialchars($camp['google_map_link']); ?>" target="_blank"
                                class="block w-full text-center py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 hover:from-primary hover:to-secondary hover:text-white transition-all duration-300 text-primary font-bold rounded-xl text-sm group-hover:shadow-lg">
                                Get Directions
                            </a>
                        <?php else: ?>
                            <button disabled class="w-full py-3 bg-gray-100 text-gray-400 rounded-xl text-sm cursor-not-allowed">
                                Location Pending
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section - Enhanced -->
    <section class="max-w-7xl mx-auto px-6 mb-24">
        <div
            class="bg-gradient-to-br from-primary via-primary to-secondary rounded-3xl p-12 md:p-20 relative overflow-hidden shadow-2xl">
            <div
                class="absolute top-0 right-0 -mr-20 -mt-20 size-80 bg-white/10 rounded-full blur-3xl animate-pulse-glow">
            </div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 size-80 bg-white/10 rounded-full blur-3xl animate-pulse-glow"
                style="animation-delay: 1s"></div>
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=" 60" height="60" viewBox="0 0 60 60"
                xmlns="http://www.w3.org/2000/svg" %3E%3Cg fill="none" fill-rule="evenodd" %3E%3Cg fill="%23ffffff"
                fill-opacity="0.05" %3E%3Cpath
                d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"
                /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-10"></div>

            <div class="relative z-10 flex flex-col items-center text-center max-w-3xl mx-auto">
                <h2 class="text-4xl md:text-6xl font-black text-white mb-8">Ready to take control of your health
                    journey?</h2>
                <p class="text-white/90 text-xl mb-12 leading-relaxed">Join millions of citizens already using NCHSP to
                    manage their digital health records and access free care.</p>
                <div class="flex flex-col sm:flex-row gap-6 w-full justify-center">
                    <a href="register.php"
                        class="group bg-white text-primary px-12 py-5 rounded-2xl text-lg font-bold hover:shadow-2xl hover:shadow-white/20 transition-all duration-300 shadow-xl hover:-translate-y-1">
                        <span class="inline-flex items-center gap-2">
                            Register Now
                            <span
                                class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </span>
                    </a>
                    <button
                        class="group bg-transparent border-2 border-white/40 text-white px-12 py-5 rounded-2xl text-lg font-bold hover:bg-white/10 hover:border-white/60 transition-all duration-300 hover:-translate-y-1">
                        <span class="inline-flex items-center gap-2">
                            Contact Support
                            <span
                                class="material-symbols-outlined group-hover:rotate-12 transition-transform">support_agent</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer - Enhanced -->
    <footer
        class="bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-background-dark border-t border-gray-100 dark:border-gray-800 py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="size-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center text-white shadow-lg">
                            <span class="material-symbols-outlined text-xl">health_and_safety</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-black tracking-tight text-primary">NCHSP</h2>
                            <p class="text-xs text-gray-500">National Portal</p>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed mb-6">The National Citizen Health Service Portal is
                        a
                        government initiative to digitalize healthcare records and provide accessible medical services
                        to all.</p>
                    <div class="flex items-center gap-4">
                        <span
                            class="material-symbols-outlined text-gray-400 hover:text-primary cursor-pointer transition-colors hover:scale-110">social_leaderboard</span>
                        <span
                            class="material-symbols-outlined text-gray-400 hover:text-primary cursor-pointer transition-colors hover:scale-110">share</span>
                        <span
                            class="material-symbols-outlined text-gray-400 hover:text-primary cursor-pointer transition-colors hover:scale-110">rss_feed</span>
                    </div>
                </div>
                <div>
                    <h5 class="font-bold mb-6 text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-sm">widgets</span>
                        Resources
                    </h5>
                    <ul class="space-y-4 text-sm text-gray-500">
                        <li><a class="hover:text-primary transition-colors flex items-center gap-2 group"
                                href="privacy_policy.php">
                                <span
                                    class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
                                Privacy Policy
                            </a></li>
                        <li><a class="hover:text-primary transition-colors flex items-center gap-2 group"
                                href="terms.php">
                                <span
                                    class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
                                Terms of Service
                            </a></li>
                    </ul>
                </div>
                <div>
                    <!-- Quick Links Removed as per request -->
                    <!-- Kept structure if we want to add back later, or I can remove the whole column if preferred, but for now I'll just keep the structure empty or remove the list items -->
                    <!-- Actually, the user asked to remove "Doctor log in", "Verify records", "Citizen", which were in Quick Links. "Documentation", "API" were in Resources. 
                         The "Resources" block had Doc, API, Privacy, Terms. I kept Privacy and Terms.
                         The "Quick Links" block had About, Find a Camp, Verify Records, Doctor Login, Citizen Support.
                         The user said "Back/home button" -> that's for the new page.
                         "Remove these buttons: Documentation, API for providers, verify records, doctor log in, citizen."
                         So I will remove the specific items. 
                    -->
                    <h5 class="font-bold mb-6 text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-sm">link</span>
                        Quick Links
                    </h5>
                    <ul class="space-y-4 text-sm text-gray-500">
                        <li><a class="hover:text-primary transition-colors flex items-center gap-2 group"
                                href="about.php">
                                <span
                                    class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
                                About Us
                            </a></li>
                        <li><a class="hover:text-primary transition-colors flex items-center gap-2 group" href="#camps">
                                <span
                                    class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
                                Find a Camp
                            </a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-bold mb-6 text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-sm">notifications</span>
                        Stay Updated
                    </h5>
                    <p class="text-gray-500 text-sm mb-4">Subscribe to our newsletter for the latest updates.</p>
                    <div class="flex gap-2">
                        <input
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl flex-1 focus:ring-2 focus:ring-primary focus:border-transparent text-sm px-4 py-3"
                            placeholder="Your email" type="email" />
                        <button
                            class="bg-gradient-to-r from-primary to-secondary text-white p-3 rounded-xl hover:shadow-lg transition-shadow">
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </div>
                </div>
            </div>
            <div
                class="pt-8 border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-400 text-sm">© 2023 National Citizen Health Service Portal. All rights reserved.</p>
                <p class="text-gray-400 text-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">verified</span>
                    Government Certified Platform
                </p>
            </div>
        </div>
    </footer>
</body>

</html>