<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name']; // Assumes session was set on login

// Fetch All Reports
$stmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY test_date DESC");
$stmt->execute([$user_id]);
$reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
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
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        .report-row {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
        }

        .report-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border-left-color: var(--primary);
            background: linear-gradient(to right, #f8fafc, #ffffff);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-normal {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-abnormal {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .pulse-dot {
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

        .shadow-soft {
            box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body>
    <!-- Enhanced Navbar -->
    <nav class="gradient-bg px-6 py-4 shadow-soft">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="dashboard.php"
                    class="flex items-center gap-2 text-white font-semibold hover:bg-white/10 transition-all duration-200 px-4 py-2 rounded-lg group">
                    <span
                        class="material-symbols-outlined group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    <span>Back to Dashboard</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div class="text-white">
                        <div class="font-semibold"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="text-sm text-white/80 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">verified_user</span>
                            Patient Portal
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Medical Test Reports</h1>
                <p class="text-gray-600">View and manage all your laboratory test results in one place</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-lg">
                    <span class="material-symbols-outlined text-blue-600">inventory</span>
                    <span class="text-sm font-medium text-gray-700">
                        Total Reports: <span class="font-bold text-blue-600"><?php echo count($reports); ?></span>
                    </span>
                </div>
                <button onclick="window.print()"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">print</span>
                    <span class="text-sm font-medium">Print</span>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card rounded-xl p-6 shadow-soft">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Recent Report</p>
                        <p class="text-xl font-bold text-gray-900">
                            <?php echo count($reports) > 0 ? htmlspecialchars($reports[0]['test_name']) : 'N/A'; ?>
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-blue-500 text-3xl">new_releases</span>
                </div>
            </div>
            <div class="glass-card rounded-xl p-6 shadow-soft">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Last Updated</p>
                        <p class="text-xl font-bold text-gray-900">
                            <?php echo count($reports) > 0 ? date('M d, Y', strtotime($reports[0]['test_date'])) : 'N/A'; ?>
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-green-500 text-3xl">calendar_today</span>
                </div>
            </div>
            <div class="glass-card rounded-xl p-6 shadow-soft">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Access Status</p>
                        <p class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <span class="pulse-dot w-2 h-2 rounded-full bg-green-500"></span>
                            All Access
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-purple-500 text-3xl">shield</span>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="glass-card rounded-xl shadow-soft overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Test Results History</h2>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span class="material-symbols-outlined text-base">info</span>
                        Sorted by most recent first
                    </div>
                </div>
            </div>

            <?php if (count($reports) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-sm text-gray-500 font-semibold uppercase tracking-wider">
                                <th class="px-6 py-4">Test Details</th>
                                <th class="px-6 py-4">Result & Range</th>
                                <th class="px-6 py-4">Doctor</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($reports as $report):
                                // Determine status based on result (this is a simple example)
                                $result_value = floatval($report['result_value']);
                                $range_parts = explode('-', $report['reference_range']);
                                $is_normal = true;
                                if (count($range_parts) == 2) {
                                    $min = floatval($range_parts[0]);
                                    $max = floatval($range_parts[1]);
                                    $is_normal = ($result_value >= $min && $result_value <= $max);
                                }
                                ?>
                                <tr class="report-row">
                                    <td class="px-6 py-5">
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined text-blue-600">lab_profile</span>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 mb-1">
                                                    <?php echo htmlspecialchars($report['test_name']); ?>
                                                </div>
                                                <div class="flex items-center gap-3 text-sm text-gray-500">
                                                    <span class="flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-sm">event</span>
                                                        <?php echo htmlspecialchars($report['test_date']); ?>
                                                    </span>
                                                    <span
                                                        class="status-badge <?php echo $is_normal ? 'status-normal' : 'status-abnormal'; ?>">
                                                        <?php echo $is_normal ? 'Normal' : 'Review Needed'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div>
                                            <div class="flex items-baseline gap-2 mb-1">
                                                <span
                                                    class="text-2xl font-bold text-blue-600"><?php echo htmlspecialchars($report['result_value']); ?></span>
                                                <span class="text-sm text-gray-500">units</span>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                Reference: <?php echo htmlspecialchars($report['reference_range']); ?>
                                            </div>
                                            <?php if (!$is_normal): ?>
                                                <div class="mt-2 flex items-center gap-1 text-sm text-amber-600">
                                                    <span class="material-symbols-outlined text-sm">warning</span>
                                                    Outside normal range
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-purple-600">person</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($report['doctor_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">Consulting Physician</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <?php if ($report['report_file']): ?>
                                                <a href="<?php echo htmlspecialchars($report['report_file']); ?>" target="_blank"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                                                    <span class="material-symbols-outlined text-lg">download</span>
                                                    Download PDF
                                                </a>
                                                <button
                                                    onclick="window.open('<?php echo htmlspecialchars($report['report_file']); ?>', '_blank')"
                                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Preview">
                                                    <span class="material-symbols-outlined">visibility</span>
                                                </button>
                                            <?php else: ?>
                                                <span
                                                    class="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-500 rounded-lg text-sm">
                                                    <span class="material-symbols-outlined text-sm">block</span>
                                                    No File
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-5xl">description</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Reports Available</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-8">
                        You don't have any medical test reports yet. Reports will appear here once they are processed.
                    </p>
                    <a href="dashboard.php"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <span class="material-symbols-outlined">home</span>
                        Return to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Note -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p class="flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-base">lock</span>
                2026 National Citizen Health Service Portal. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Quick Action Floating Button -->
    <div class="fixed bottom-6 right-6">
        <button onclick="scrollToTop()"
            class="w-12 h-12 rounded-full gradient-bg text-white shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center">
            <span class="material-symbols-outlined">arrow_upward</span>
        </button>
    </div>

    <script>
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('.report-row');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
        });
    </script>
</body>

</html>