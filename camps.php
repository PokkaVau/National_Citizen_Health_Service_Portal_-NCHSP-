<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

// Fetch Camps
$stmt = $pdo->query("SELECT * FROM health_camps ORDER BY camp_date DESC");
$camps = $stmt->fetchAll();

// Function to get status based on date
function getCampStatus($camp_date)
{
    $today = new DateTime();
    $campDate = new DateTime($camp_date);

    if ($today > $campDate) {
        return ['status' => 'Completed', 'color' => 'bg-gray-100 text-gray-700', 'badge_color' => 'bg-gray-500'];
    } elseif ($today->format('Y-m-d') == $campDate->format('Y-m-d')) {
        return ['status' => 'Today', 'color' => 'bg-green-100 text-green-700', 'badge_color' => 'bg-green-500'];
    } elseif ($campDate->diff($today)->days <= 7) {
        return ['status' => 'Upcoming', 'color' => 'bg-blue-100 text-blue-700', 'badge_color' => 'bg-blue-500'];
    } else {
        return ['status' => 'Scheduled', 'color' => 'bg-purple-100 text-purple-700', 'badge_color' => 'bg-purple-500'];
    }
}

// Function to get time remaining
function getTimeRemaining($camp_date)
{
    $today = new DateTime();
    $campDate = new DateTime($camp_date);

    if ($today > $campDate) {
        return 'Completed';
    }

    $interval = $today->diff($campDate);

    if ($interval->days == 0) {
        return 'Today';
    } elseif ($interval->days == 1) {
        return 'Tomorrow';
    } elseif ($interval->days < 7) {
        return $interval->days . ' days left';
    } elseif ($interval->days < 30) {
        return floor($interval->days / 7) . ' weeks left';
    } else {
        return floor($interval->days / 30) . ' months left';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Camps - HealthPortal</title>
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

        .camp-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .camp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), #3b82f6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .camp-card:hover::before {
            opacity: 1;
        }

        .camp-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(19, 127, 236, 0.1);
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

        .map-container {
            position: relative;
            height: 200px;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .map-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.3));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .date-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }

        .camp-type-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .camp-card:hover .camp-type-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .empty-state {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .scroll-animation {
            animation: scrollReveal 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes scrollReveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-btn {
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--color-primary) 0%, #3b82f6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(19, 127, 236, 0.2);
        }

        .search-input {
            transition: all 0.3s ease;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(19, 127, 236, 0.1);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Navigation -->
    <nav class="glass-card sticky top-0 z-20 border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="dashboard.php"
                        class="flex items-center gap-3 text-slate-600 hover:text-primary transition-colors group">
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-r from-primary/10 to-blue-500/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-primary">arrow_back</span>
                        </div>
                        <div>
                            <span class="font-medium">Back to Dashboard</span>
                            <p class="text-xs text-slate-500">Return to health overview</p>
                        </div>
                    </a>
                </div>

                <div class="text-center sm:text-right">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-grey-600 bg-clip-text text-black">
                        Health Camps & Events
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">Find nearby medical camps and health initiatives</p>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Stats -->
        <div class="glass-card rounded-2xl border border-slate-200/50 p-6 mb-8">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Discover Health Camps</h2>
                    <p class="text-slate-600">
                        Find free medical checkups, vaccination drives, and health awareness programs in your area.
                        These camps are organized by government and healthcare partners.
                    </p>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary"><?php echo count($camps); ?></div>
                        <div class="text-sm text-slate-500">Total Camps</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">
                            <?php
                            $upcoming = 0;
                            foreach ($camps as $camp) {
                                $status = getCampStatus($camp['camp_date']);
                                if (in_array($status['status'], ['Today', 'Upcoming', 'Scheduled'])) {
                                    $upcoming++;
                                }
                            }
                            echo $upcoming;
                            ?>
                        </div>
                        <div class="text-sm text-slate-500">Upcoming</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="glass-card rounded-2xl border border-slate-200/50 p-6 mb-8">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex-1 w-full lg:w-auto">
                    <div class="relative">
                        <input type="text" placeholder="Search camps by name, location, or type..."
                            class="search-input w-full lg:w-96 pl-12 pr-4 py-3 bg-gradient-to-r from-slate-50 to-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all duration-300">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        class="filter-btn px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-300 text-sm font-medium">
                        All Camps
                    </button>
                    <button
                        class="filter-btn px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-300 text-sm font-medium">
                        Upcoming
                    </button>
                    <button
                        class="filter-btn px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-300 text-sm font-medium">
                        Today
                    </button>
                    <button
                        class="filter-btn px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all duration-300 text-sm font-medium">
                        Vaccination
                    </button>
                </div>
            </div>
        </div>

        <!-- Camps Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php if (count($camps) > 0): ?>
                <?php
                $delay = 0;
                foreach ($camps as $camp):
                    $status = getCampStatus($camp['camp_date']);
                    $timeRemaining = getTimeRemaining($camp['camp_date']);
                    $delay += 100;
                    ?>
                    <div class="camp-card glass-card rounded-2xl border border-slate-200/50 overflow-hidden scroll-animation"
                        style="animation-delay: <?php echo $delay; ?>ms">
                        <!-- Image or Map Preview -->
                        <?php if (!empty($camp['image_path']) && file_exists($camp['image_path'])): ?>
                            <div class="h-48 w-full overflow-hidden relative">
                                <img src="<?php echo htmlspecialchars($camp['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($camp['name']); ?>"
                                    class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                                <?php if ($camp['google_map_link']): ?>
                                    <a href="<?php echo htmlspecialchars($camp['google_map_link']); ?>" target="_blank"
                                        class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-white text-primary flex items-center gap-1.5 transition-colors">
                                        <i class="fas fa-map-marked-alt text-primary"></i>
                                        View Map
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($camp['google_map_link']): ?>
                            <a href="<?php echo htmlspecialchars($camp['google_map_link']); ?>" target="_blank"
                                class="block map-container relative group overflow-hidden">
                                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110"
                                    style="background-image: url('https://maps.googleapis.com/maps/api/staticmap?center=<?php echo urlencode($camp['location']); ?>&zoom=14&size=600x300&maptype=roadmap&key=YOUR_API_KEY_HERE'); background-color: #e0e7ff;">
                                </div>
                                <div class="map-overlay backdrop-blur-[2px] bg-black/20 group-hover:bg-black/30 transition-colors">
                                    <div class="text-center transform transition-transform duration-300 group-hover:scale-110">
                                        <div
                                            class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30 text-white">
                                            <i class="fas fa-map-marked-alt text-xl"></i>
                                        </div>
                                        <p class="font-bold text-white text-lg tracking-wide shadow-black drop-shadow-md">View
                                            Location</p>
                                    </div>
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="map-container relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-100 to-slate-200"></div>
                                <div class="map-overlay">
                                    <div class="text-center text-slate-400">
                                        <i class="fas fa-map-marker-alt text-3xl mb-2 opacity-50"></i>
                                        <p class="font-medium"><?php echo htmlspecialchars($camp['location']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Date Badge -->
                        <div class="date-badge">
                            <div class="text-xs text-slate-500">Date</div>
                            <div class="font-bold text-slate-900">
                                <?php echo date('M j, Y', strtotime($camp['camp_date'])); ?>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-6">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="camp-type-icon bg-gradient-to-br from-primary/10 to-blue-500/10 text-primary">
                                    <i class="fas fa-hospital"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-start justify-between">
                                        <h3 class="text-xl font-bold text-slate-900 mb-2">
                                            <?php echo htmlspecialchars($camp['name']); ?>
                                        </h3>
                                        <span class="status-badge <?php echo $status['color']; ?>">
                                            <?php echo $status['status']; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-slate-600 mb-3">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $timeRemaining; ?></span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-slate-600 text-sm mb-6 line-clamp-3">
                                <?php echo nl2br(htmlspecialchars($camp['description'])); ?>
                            </p>

                            <!-- Location and Contact -->
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Location</p>
                                        <p class="text-sm font-medium text-slate-900">
                                            <?php echo htmlspecialchars($camp['location']); ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if ($camp['contact_number']): ?>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-50 to-green-100 text-green-600 flex items-center justify-center">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-500">Contact</p>
                                            <p class="text-sm font-medium text-slate-900">
                                                <?php echo htmlspecialchars($camp['contact_number']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <?php if ($camp['google_map_link']): ?>
                                    <a href="<?php echo htmlspecialchars($camp['google_map_link']); ?>" target="_blank"
                                        class="flex-1 flex items-center justify-center gap-2 gradient-primary text-white py-3 rounded-xl font-medium hover:shadow-lg hover:shadow-primary/30 transition-all duration-300">
                                        <i class="fas fa-directions"></i>
                                        Get Directions
                                    </a>
                                <?php else: ?>
                                    <button
                                        class="flex-1 flex items-center justify-center gap-2 bg-slate-100 text-slate-700 py-3 rounded-xl font-medium hover:bg-slate-200 transition-colors">
                                        <i class="fas fa-directions"></i>
                                        Get Directions
                                    </button>
                                <?php endif; ?>

                                <button
                                    class="flex items-center justify-center gap-2 w-12 bg-gradient-to-r from-slate-50 to-white border border-slate-300 text-slate-600 rounded-xl hover:border-primary hover:text-primary transition-all duration-300">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="col-span-3 empty-state">
                    <div class="glass-card rounded-2xl border border-slate-200/50 p-12 text-center">
                        <div
                            class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                            <i class="fas fa-calendar-times text-slate-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">No Health Camps Available</h3>
                        <p class="text-slate-600 mb-8 max-w-md mx-auto">
                            There are currently no scheduled health camps in your area.
                            Check back later for upcoming medical camps and health initiatives.
                        </p>
                        <button
                            class="gradient-primary text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 inline-flex items-center gap-2">
                            <i class="fas fa-bell"></i>
                            Notify Me
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Information Section -->
        <?php if (count($camps) > 0): ?>
            <div class="glass-card rounded-2xl border border-slate-200/50 p-6 mt-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100/50 rounded-xl">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                            <h4 class="font-bold text-slate-900">What to Bring</h4>
                        </div>
                        <ul class="text-sm text-slate-600 space-y-1">
                            <li class="flex items-center gap-2">
                                <i class="fas fa-check text-green-500"></i>
                                Government ID proof
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-check text-green-500"></i>
                                Previous medical records
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-check text-green-500"></i>
                                Wear comfortable clothing
                            </li>
                        </ul>
                    </div>

                    <div class="p-4 bg-gradient-to-r from-green-50 to-green-100/50 rounded-xl">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                <i class="fas fa-stethoscope text-white"></i>
                            </div>
                            <h4 class="font-bold text-slate-900">Services Offered</h4>
                        </div>
                        <ul class="text-sm text-slate-600 space-y-1">
                            <li class="flex items-center gap-2">
                                <i class="fas fa-syringe text-blue-500"></i>
                                Free vaccinations
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-heartbeat text-red-500"></i>
                                Basic health checkups
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-user-md text-purple-500"></i>
                                Doctor consultations
                            </li>
                        </ul>
                    </div>

                    <div class="p-4 bg-gradient-to-r from-purple-50 to-purple-100/50 rounded-xl">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <h4 class="font-bold text-slate-900">Timings</h4>
                        </div>
                        <ul class="text-sm text-slate-600 space-y-1">
                            <li class="flex items-center gap-2">
                                <i class="fas fa-sun text-yellow-500"></i>
                                Usually 9 AM - 5 PM
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-calendar-day text-orange-500"></i>
                                Weekdays & weekends
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-utensils text-gray-500"></i>
                                Bring snacks/water
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <div
            class="glass-card rounded-2xl border border-slate-200/50 p-8 mt-8 bg-gradient-to-r from-primary/5 to-blue-500/5">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Need Medical Assistance?</h3>
                    <p class="text-slate-600">
                        Can't find a suitable health camp? Book an appointment with our partner hospitals
                        for comprehensive medical checkups and consultations.
                    </p>
                </div>
                <a href="book_appointment.php"
                    class="gradient-primary text-white px-8 py-3 rounded-xl font-semibold hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center gap-3 group whitespace-nowrap">
                    <i class="fas fa-calendar-plus group-hover:scale-110 transition-transform"></i>
                    Book Appointment
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="book_appointment.php" class="fixed bottom-8 right-8 z-30">
        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-r from-primary to-blue-600 rounded-full blur-md opacity-70">
            </div>
            <div
                class="relative w-16 h-16 rounded-full gradient-primary flex items-center justify-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110">
                <i class="fas fa-hospital text-white text-xl"></i>
            </div>
        </div>
    </a>

    <script>
        // Initialize scroll animations
        document.addEventListener('DOMContentLoaded', function () {
            // Add scroll animation to cards
            const cards = document.querySelectorAll('.scroll-animation');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                observer.observe(card);
            });

            // Search functionality
            const searchInput = document.querySelector('input[type="text"]');
            const campCards = document.querySelectorAll('.camp-card');

            if (searchInput && campCards.length > 0) {
                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();

                    campCards.forEach(card => {
                        const title = card.querySelector('h3').textContent.toLowerCase();
                        const location = card.querySelector('p.text-sm.font-medium').textContent.toLowerCase();
                        const description = card.querySelector('p.line-clamp-3').textContent.toLowerCase();

                        if (title.includes(searchTerm) || location.includes(searchTerm) || description.includes(searchTerm)) {
                            card.style.display = 'block';
                            // Trigger animation
                            card.style.animation = 'none';
                            setTimeout(() => {
                                card.style.animation = 'scrollReveal 0.6s ease-out forwards';
                            }, 10);
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

            // Filter buttons
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    // Here you could add filter logic based on button text
                    const filterType = this.textContent.trim();
                    // Implement filter logic based on your needs
                });
            });

            // Make first filter button active
            if (filterButtons.length > 0) {
                filterButtons[0].classList.add('active');
            }

            // Share button functionality
            const shareButtons = document.querySelectorAll('button:has(.fa-share-alt)');
            shareButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const card = this.closest('.camp-card');
                    const title = card.querySelector('h3').textContent;
                    const location = card.querySelector('p.text-sm.font-medium').textContent;
                    const date = card.querySelector('.date-badge .font-bold').textContent;

                    const shareText = `Health Camp: ${title}\nLocation: ${location}\nDate: ${date}\n\nCheck it out on HealthPortal!`;

                    if (navigator.share) {
                        navigator.share({
                            title: title,
                            text: shareText,
                            url: window.location.href
                        });
                    } else {
                        // Fallback: Copy to clipboard
                        navigator.clipboard.writeText(shareText).then(() => {
                            const originalHTML = this.innerHTML;
                            this.innerHTML = '<i class="fas fa-check"></i>';
                            this.classList.add('bg-green-500', 'text-white', 'border-green-500');

                            setTimeout(() => {
                                this.innerHTML = originalHTML;
                                this.classList.remove('bg-green-500', 'text-white', 'border-green-500');
                            }, 2000);
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>