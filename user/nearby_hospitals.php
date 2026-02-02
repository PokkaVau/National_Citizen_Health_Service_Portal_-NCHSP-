<?php
require('../config/db.php');
require('../auth_session.php');
check_user_login();

// Handle Booking Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_blood'])) {
    $hospital_id = $_POST['hospital_id'];
    $blood_group = $_POST['blood_group'];
    $units = (int) $_POST['units'];
    $user_id = $_SESSION['user_id'];

    if ($units > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO blood_bookings (user_id, hospital_id, blood_group, units) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $hospital_id, $blood_group, $units]);
            $success_msg = "Booking request sent successfully! The hospital will review it shortly.";
        } catch (PDOException $e) {
            $error_msg = "Error submitting request: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please enter a valid quantity.";
    }
}

// Fetch Hospitals and Inventory
$stmt = $pdo->query("
    SELECT h.*, 
    GROUP_CONCAT(CONCAT(hi.blood_group, ':', hi.quantity) SEPARATOR ', ') as inventory
    FROM hospitals h
    LEFT JOIN hospital_inventory hi ON h.id = hi.hospital_id
    GROUP BY h.id
    ORDER BY h.name
");
$hospitals = $stmt->fetchAll();

// Calculate total hospitals
$total_hospitals = count($hospitals);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nearby Hospitals - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fdf2f8 0%, #f0f9ff 100%);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in {
            animation: slideIn 0.5s ease-out;
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(239, 68, 68, 0.15);
        }

        .blood-group-badge {
            position: relative;
            overflow: hidden;
        }

        .blood-group-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .pulse {
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

        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .gradient-text {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            border: 1px solid #fee2e2;
        }
    </style>
</head>

<body class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header with Stats -->
        <div class="slide-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div class="flex items-center gap-4">
                    <a href="../dashboard.php"
                        class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 hover:bg-red-50 hover:border-red-200 transition-all duration-300 flex items-center justify-center text-slate-600 hover:text-red-600">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Hospital Blood Reserve</h1>
                        <p class="text-slate-500 mt-1">Check availability and book blood from nearby hospitals</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hospital text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500">Available Hospitals</p>
                                <p class="text-2xl font-bold text-slate-900"><?php echo $total_hospitals; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($success_msg)): ?>
                <div id="success-alert"
                    class="mb-6 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center justify-between slide-in">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-xl"></i>
                        <span class="font-medium"><?php echo $success_msg; ?></span>
                    </div>
                    <button onclick="document.getElementById('success-alert').style.display='none'"
                        class="text-white/80 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_msg)): ?>
                <div id="error-alert"
                    class="mb-6 bg-gradient-to-r from-red-500 to-pink-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center justify-between slide-in">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                        <span class="font-medium"><?php echo $error_msg; ?></span>
                    </div>
                    <button onclick="document.getElementById('error-alert').style.display='none'"
                        class="text-white/80 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Search and Filter Bar -->
        <div class="mb-8 slide-in">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="w-full md:w-auto">
                        <div class="relative">
                            <i
                                class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" placeholder="Search hospitals by name or city..."
                                class="w-full md:w-80 pl-12 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all"
                                id="searchInput">
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="fas fa-filter text-red-500"></i>
                            <span>Filter:</span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="filterByStock('all')"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-all">
                                All
                            </button>
                            <button onclick="filterByStock('available')"
                                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-all">
                                Available Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hospitals Grid -->
        <?php if ($total_hospitals > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="hospitalsGrid">
                <?php foreach ($hospitals as $index => $h): ?>
                    <div class="card-gradient rounded-2xl shadow-sm overflow-hidden hover-lift slide-in"
                        style="animation-delay: <?php echo $index * 0.1; ?>s"
                        data-name="<?php echo htmlspecialchars(strtolower($h['name'])); ?>"
                        data-city="<?php echo htmlspecialchars(strtolower($h['city'])); ?>"
                        data-stock="<?php echo $h['inventory'] ? 'available' : 'limited'; ?>">
                        <div class="p-6">
                            <!-- Hospital Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-14 h-14 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-hospital text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900 mb-1">
                                            <?php echo htmlspecialchars($h['name']); ?>
                                        </h3>
                                        <div class="flex items-center gap-2 text-sm text-slate-500">
                                            <i class="fas fa-map-marker-alt text-red-500"></i>
                                            <?php echo htmlspecialchars($h['city']); ?>
                                            <span class="text-slate-300">•</span>
                                            <i class="fas fa-phone-alt text-slate-400"></i>
                                            <?php echo htmlspecialchars($h['contact_number']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="mb-6 p-3 bg-red-50 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-location-dot text-red-500 mt-1"></i>
                                    <p class="text-sm text-slate-700"><?php echo htmlspecialchars($h['address']); ?></p>
                                </div>
                            </div>

                            <!-- Blood Stock -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h4
                                        class="text-sm font-semibold text-slate-600 uppercase tracking-wider flex items-center gap-2">
                                        <i class="fas fa-tint text-red-500"></i>
                                        Available Blood Stock
                                    </h4>
                                    <?php if ($h['inventory']): ?>
                                        <span class="text-xs font-medium bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                            <i class="fas fa-check-circle text-xs mr-1"></i>
                                            Stock Available
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <?php
                                    if ($h['inventory']) {
                                        $items = explode(', ', $h['inventory']);
                                        foreach ($items as $item) {
                                            list($bg, $qty) = explode(':', $item);
                                            $qty = (int) $qty;
                                            $status_class = $qty > 5 ? 'bg-green-50 border-green-200 text-green-800' :
                                                ($qty > 0 ? 'bg-yellow-50 border-yellow-200 text-yellow-800' :
                                                    'bg-slate-50 border-slate-200 text-slate-500');
                                            if ($qty > 0) {
                                                echo "<div class='blood-group-badge $status_class rounded-lg border p-3 flex items-center justify-between'>";
                                                echo "<div>";
                                                echo "<span class='text-lg font-bold text-red-600'>$bg</span>";
                                                echo "<div class='text-xs text-slate-400 mt-1'>Blood Type</div>";
                                                echo "</div>";
                                                echo "<div class='text-right'>";
                                                echo "<span class='text-xl font-bold'>$qty</span>";
                                                echo "<div class='text-xs text-slate-400'>Units</div>";
                                                echo "</div>";
                                                echo "</div>";
                                            }
                                        }
                                    } else {
                                        echo "<div class='col-span-2 text-center py-8 bg-slate-50 rounded-xl'>";
                                        echo "<i class='fas fa-exclamation-triangle text-slate-400 text-3xl mb-3'></i>";
                                        echo "<p class='text-slate-500 font-medium'>No stock information available</p>";
                                        echo "<p class='text-sm text-slate-400 mt-1'>Contact hospital for details</p>";
                                        echo "</div>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="pt-4 border-t border-slate-100">
                                <button
                                    onclick="openBookModal(<?php echo $h['id']; ?>, '<?php echo htmlspecialchars(addslashes($h['name'])); ?>')"
                                    class="w-full bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-semibold py-3.5 rounded-xl transition-all duration-300 shadow hover:shadow-lg flex items-center justify-center gap-2 group">
                                    <i class="fas fa-syringe text-lg"></i>
                                    <span>Book Blood Request</span>
                                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty Search State -->
            <div id="noResults" class="hidden text-center py-12 slide-in">
                <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-hospital text-slate-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-700 mb-2">No hospitals found</h3>
                <p class="text-slate-500 mb-6">Try adjusting your search criteria</p>
            </div>
        <?php else: ?>
            <div class="text-center py-16 slide-in">
                <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-hospital text-slate-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-700 mb-2">No hospitals available</h3>
                <p class="text-slate-500 mb-6">Check back later for hospital listings</p>
            </div>
        <?php endif; ?>

        <!-- Footer Info -->
        <div class="mt-12 pt-8 border-t border-slate-200 slide-in">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-info-circle text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-900">Important Information</h4>
                            <p class="text-sm text-slate-500 mt-1">Booking requests are reviewed by hospital staff.
                                You'll receive a confirmation once approved.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            <span>Good Stock</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                            <span>Low Stock</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span class="w-3 h-3 bg-red-500 rounded-full pulse"></span>
                            <span>Emergency Need</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Booking Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md slide-in">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-syringe text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Request Blood</h2>
                        <p class="text-sm text-slate-500">From <span id="modalHospitalName"
                                class="font-semibold text-red-600"></span></p>
                    </div>
                </div>
            </div>

            <form method="POST" id="bookingForm">
                <input type="hidden" name="book_blood" value="1">
                <input type="hidden" name="hospital_id" id="modalHospitalId">

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fas fa-tint text-red-500 mr-2"></i>
                            Blood Group Required *
                        </label>
                        <div class="relative">
                            <i
                                class="fas fa-droplet absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <select name="blood_group" required
                                class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all appearance-none bg-white">
                                <option value="">Select blood group</option>
                                <option value="A+">A Positive (A+)</option>
                                <option value="A-">A Negative (A-)</option>
                                <option value="B+">B Positive (B+)</option>
                                <option value="B-">B Negative (B-)</option>
                                <option value="AB+">AB Positive (AB+)</option>
                                <option value="AB-">AB Negative (AB-)</option>
                                <option value="O+">O Positive (O+)</option>
                                <option value="O-">O Negative (O-)</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fas fa-weight-scale text-red-500 mr-2"></i>
                            Quantity Needed *
                        </label>
                        <div class="relative">
                            <i
                                class="fas fa-flask absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="number" name="units" min="1" max="10" required placeholder="Enter units (1-10)"
                                class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            <i class="fas fa-info-circle text-slate-400 mr-1"></i>
                            Standard unit is approximately 450ml
                        </p>
                    </div>

                    <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-clock text-red-500 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Processing Time</p>
                                <p class="text-xs text-slate-500 mt-1">Hospital will review your request within 24
                                    hours. You'll be notified once approved.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 rounded-b-2xl">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeBookModal()"
                            class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-xl hover:from-red-700 hover:to-pink-700 transition-all duration-300 shadow hover:shadow-lg font-medium flex items-center gap-2">
                            <i class="fas fa-paper-plane"></i>
                            Submit Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openBookModal(id, name) {
            document.getElementById('modalHospitalId').value = id;
            document.getElementById('modalHospitalName').textContent = name;
            document.getElementById('bookingModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeBookModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const hospitals = document.querySelectorAll('#hospitalsGrid > div');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;

            hospitals.forEach(hospital => {
                const name = hospital.getAttribute('data-name');
                const city = hospital.getAttribute('data-city');

                if (name.includes(searchTerm) || city.includes(searchTerm)) {
                    hospital.style.display = 'block';
                    visibleCount++;
                } else {
                    hospital.style.display = 'none';
                }
            });

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });

        function filterByStock(filter) {
            const hospitals = document.querySelectorAll('#hospitalsGrid > div');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;

            hospitals.forEach(hospital => {
                const stock = hospital.getAttribute('data-stock');

                if (filter === 'all' || (filter === 'available' && stock === 'available')) {
                    hospital.style.display = 'block';
                    visibleCount++;
                } else {
                    hospital.style.display = 'none';
                }
            });

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (event) {
            const modal = document.getElementById('bookingModal');
            if (modal && !modal.classList.contains('hidden') && event.target === modal) {
                closeBookModal();
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const successAlert = document.getElementById('success-alert');
            const errorAlert = document.getElementById('error-alert');

            if (successAlert) {
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 300);
            }

            if (errorAlert) {
                errorAlert.style.opacity = '0';
                setTimeout(() => errorAlert.remove(), 300);
            }
        }, 5000);

        // Form validation
        document.getElementById('bookingForm')?.addEventListener('submit', function (e) {
            const units = this.querySelector('input[name="units"]');
            if (units && (units.value < 1 || units.value > 10)) {
                e.preventDefault();
                alert('Please enter a quantity between 1 and 10 units.');
                units.focus();
            }
        });
    </script>
</body>

</html>