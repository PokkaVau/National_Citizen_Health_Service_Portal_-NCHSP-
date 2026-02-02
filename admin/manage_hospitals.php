<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_hospital'])) {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $contact = $_POST['contact'];

        try {
            $stmt = $pdo->prepare("INSERT INTO hospitals (name, address, city, contact_number) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $address, $city, $contact]);
            $message = "Hospital added successfully!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_hospital'])) {
        $id = $_POST['hospital_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM hospitals WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Hospital deleted successfully!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_hospital'])) {
        $id = $_POST['hospital_id'];
        $name = $_POST['name'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $contact = $_POST['contact'];

        try {
            $stmt = $pdo->prepare("UPDATE hospitals SET name = ?, address = ?, city = ?, contact_number = ? WHERE id = ?");
            $stmt->execute([$name, $address, $city, $contact, $id]);
            $message = "Hospital updated successfully!";
        } catch (PDOException $e) {
            $message = "Error updating: " . $e->getMessage();
        }
    }
}

// Fetch Hospitals
$stmt = $pdo->query("SELECT * FROM hospitals ORDER BY created_at DESC");
$hospitals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospitals - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        /* Smooth transitions */
        .transition-all {
            transition: all 0.3s ease;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Card hover effects */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Status badge animation */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Enhanced Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-slate-900 to-slate-800 text-white flex flex-col shadow-xl">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-10">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-heartbeat text-white text-lg"></i>
                    </div>
                    <div>
                        <span
                            class="text-xl font-bold bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">Admin
                            Portal</span>
                        <p class="text-xs text-slate-400 mt-1">Hospital Management</p>
                    </div>
                </div>
                <nav class="space-y-1">
                    <a href="dashboard.php"
                        class="flex items-center gap-3 px-4 py-3.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                        <div
                            class="w-8 h-8 flex items-center justify-center bg-white/0 group-hover:bg-blue-500/20 rounded-lg transition-all">
                            <i class="fas fa-home"></i>
                        </div>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="manage_hospitals.php"
                        class="flex items-center gap-3 px-4 py-3.5 bg-gradient-to-r from-blue-600/20 to-indigo-600/20 text-white border-l-4 border-blue-500 rounded-xl transition-all">
                        <div class="w-8 h-8 flex items-center justify-center bg-blue-500 rounded-lg">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <span class="font-medium">Hospitals</span>
                        <span
                            class="ml-auto bg-blue-500 text-xs px-2 py-1 rounded-full"><?php echo count($hospitals); ?></span>
                    </a>
                    <a href="manage_representatives.php"
                        class="flex items-center gap-3 px-4 py-3.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                        <div
                            class="w-8 h-8 flex items-center justify-center bg-white/0 group-hover:bg-purple-500/20 rounded-lg transition-all">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="font-medium">Representatives</span>
                    </a>
                    <a href="manage_users.php"
                        class="flex items-center gap-3 px-4 py-3.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                        <div
                            class="w-8 h-8 flex items-center justify-center bg-white/0 group-hover:bg-green-500/20 rounded-lg transition-all">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="font-medium">Users</span>
                    </a>
                    <a href="manage_blood_requests.php"
                        class="flex items-center gap-3 px-4 py-3.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                        <div
                            class="w-8 h-8 flex items-center justify-center bg-white/0 group-hover:bg-red-500/20 rounded-lg transition-all">
                            <i class="fas fa-hand-holding-medical"></i>
                        </div>
                        <span class="font-medium">Requests</span>
                    </a>
                </nav>

                <div class="mt-12 pt-6 border-t border-slate-700/50">
                    <a href="../logout.php"
                        class="flex items-center gap-3 px-4 py-3 text-red-300 hover:text-white hover:bg-red-500/10 rounded-xl transition-all duration-300 group">
                        <div
                            class="w-8 h-8 flex items-center justify-center bg-red-500/20 group-hover:bg-red-500/30 rounded-lg transition-all">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <span class="font-medium">Logout</span>
                    </a>
                </div>
            </div>

            <!-- Sidebar footer -->
            <div class="mt-auto p-6 border-t border-slate-700/50">
                <div class="text-center text-slate-400 text-sm">
                    <p>Hospital Management System</p>
                    <p class="text-xs mt-1">v2.1.0</p>
                </div>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-7xl mx-auto space-y-8 slide-in">
                <!-- Header Section -->
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-hospital text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-slate-900">Manage Hospitals</h1>
                                <p class="text-slate-500">Add, edit, and manage hospital records in the system</p>
                            </div>
                        </div>
                    </div>
                    <button onclick="openAddModal()"
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover-lift flex items-center gap-3">
                        <i class="fas fa-plus"></i>
                        <span class="font-semibold">Add Hospital</span>
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover-lift">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-500 text-sm font-medium">Total Hospitals</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($hospitals); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-hospital text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-xs text-slate-500">Last updated: <?php echo date('M d, Y'); ?></p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover-lift">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-500 text-sm font-medium">Cities Covered</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2">
                                    <?php
                                    $cities = array_unique(array_column($hospitals, 'city'));
                                    echo count($cities);
                                    ?>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-city text-emerald-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-xs text-slate-500">Across different locations</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover-lift">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-500 text-sm font-medium">Active Status</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2">All Active</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 pulse"></span>
                                System Operational
                            </span>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div id="message-alert"
                        class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center justify-between slide-in">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-info-circle text-xl"></i>
                            <span><?php echo $message; ?></span>
                        </div>
                        <button onclick="document.getElementById('message-alert').style.display='none'"
                            class="text-white/80 hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Hospitals Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover-lift">
                    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">Hospital Directory</h2>
                            <div class="relative">
                                <input type="text" placeholder="Search hospitals..."
                                    class="pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                    onkeyup="filterTable(this.value)">
                                <i
                                    class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-8 py-4 text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Hospital Details</th>
                                    <th class="px-8 py-4 text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Contact</th>
                                    <th class="px-8 py-4 text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Location</th>
                                    <th
                                        class="px-8 py-4 text-right text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($hospitals as $hospital): ?>
                                    <tr class="hover:bg-slate-50/80 transition-all duration-300"
                                        data-searchable="<?php echo htmlspecialchars(strtolower($hospital['name'] . ' ' . $hospital['city'] . ' ' . $hospital['address'])); ?>">
                                        <td class="px-8 py-5">
                                            <div class="flex items-start gap-4">
                                                <div
                                                    class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center">
                                                    <i class="fas fa-hospital text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-slate-900 text-lg">
                                                        <?php echo htmlspecialchars($hospital['name']); ?>
                                                    </div>
                                                    <div class="text-sm text-slate-500 mt-1">
                                                        <i class="fas fa-map-marker-alt text-xs mr-2"></i>
                                                        <?php echo htmlspecialchars($hospital['address']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-phone text-slate-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-slate-900">
                                                        <?php echo htmlspecialchars($hospital['contact_number']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-400 mt-1">Contact Number</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="inline-flex items-center gap-2">
                                                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-city text-blue-500 text-sm"></i>
                                                </div>
                                                <span class="font-medium text-slate-700">
                                                    <?php echo htmlspecialchars($hospital['city']); ?>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                    <i class="fas fa-map-pin text-xs mr-1.5"></i>
                                                    Registered
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($hospital)); ?>)"
                                                    class="w-10 h-10 flex items-center justify-center bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-all duration-300 hover-lift"
                                                    title="Edit Hospital">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirmDelete()" class="inline">
                                                    <input type="hidden" name="delete_hospital" value="1">
                                                    <input type="hidden" name="hospital_id"
                                                        value="<?php echo $hospital['id']; ?>">
                                                    <button type="submit"
                                                        class="w-10 h-10 flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-all duration-300 hover-lift"
                                                        title="Delete Hospital">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <button
                                                    onclick="viewHospitalDetails(<?php echo htmlspecialchars(json_encode($hospital)); ?>)"
                                                    class="w-10 h-10 flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-lg transition-all duration-300 hover-lift"
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($hospitals)): ?>
                        <div class="text-center py-12">
                            <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-hospital text-slate-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-700 mb-2">No Hospitals Found</h3>
                            <p class="text-slate-500 mb-6">Start by adding your first hospital to the system</p>
                            <button onclick="openAddModal()"
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg transition-all duration-300 shadow hover:shadow-lg inline-flex items-center gap-2">
                                <i class="fas fa-plus"></i>
                                Add First Hospital
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="px-8 py-4 border-t border-slate-200 bg-slate-50/50">
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <div class="flex items-center gap-4">
                                <span class="font-medium">Total: <?php echo count($hospitals); ?> hospitals</span>
                                <span class="text-slate-400">•</span>
                                <span>Showing all records</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-database text-slate-400"></i>
                                <span>Database Updated</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md slide-in">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Add New Hospital</h2>
                        <p class="text-sm text-slate-500">Enter hospital details below</p>
                    </div>
                </div>
            </div>
            <form method="POST">
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Hospital Name *</label>
                        <div class="relative">
                            <i
                                class="fas fa-hospital absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="name" required placeholder="Enter hospital name"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">City *</label>
                        <div class="relative">
                            <i
                                class="fas fa-city absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="city" required placeholder="Enter city"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Address *</label>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-3 text-slate-400"></i>
                            <textarea name="address" required placeholder="Enter full address" rows="3"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Contact Number *</label>
                        <div class="relative">
                            <i
                                class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="contact" required placeholder="01712345678" pattern="[0-9]{11}"
                                minlength="11" maxlength="11"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 rounded-b-2xl">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeAddModal()"
                            class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-medium">
                            Cancel
                        </button>
                        <button type="submit" name="add_hospital"
                            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow hover:shadow-lg font-medium">
                            <i class="fas fa-save mr-2"></i>Add Hospital
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md slide-in">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Edit Hospital</h2>
                        <p class="text-sm text-slate-500">Update hospital information</p>
                    </div>
                </div>
            </div>
            <form method="POST">
                <input type="hidden" name="edit_hospital" value="1">
                <input type="hidden" name="hospital_id" id="edit_id">
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Hospital Name *</label>
                        <div class="relative">
                            <i
                                class="fas fa-hospital absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="name" id="edit_name" required
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">City *</label>
                        <div class="relative">
                            <i
                                class="fas fa-city absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="city" id="edit_city" required
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Address *</label>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-3 text-slate-400"></i>
                            <textarea name="address" id="edit_address" required rows="3"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Contact Number *</label>
                        <div class="relative">
                            <i
                                class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="contact" id="edit_contact" required pattern="[0-9]{11}"
                                minlength="11" maxlength="11"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 rounded-b-2xl">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEditModal()"
                            class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow hover:shadow-lg font-medium">
                            <i class="fas fa-save mr-2"></i>Update Hospital
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openEditModal(hospital) {
            document.getElementById('edit_id').value = hospital.id;
            document.getElementById('edit_name').value = hospital.name;
            document.getElementById('edit_city').value = hospital.city;
            document.getElementById('edit_address').value = hospital.address;
            document.getElementById('edit_contact').value = hospital.contact_number;
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function confirmDelete() {
            return confirm('Are you sure you want to delete this hospital? This action cannot be undone.');
        }

        function filterTable(searchTerm) {
            const rows = document.querySelectorAll('tbody tr');
            searchTerm = searchTerm.toLowerCase();

            rows.forEach(row => {
                const searchableText = row.getAttribute('data-searchable');
                if (searchableText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function viewHospitalDetails(hospital) {
            alert(`Hospital Details:\n\nName: ${hospital.name}\nCity: ${hospital.city}\nAddress: ${hospital.address}\nContact: ${hospital.contact_number}`);
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');

            if (addModal && !addModal.classList.contains('hidden')) {
                if (event.target === addModal) {
                    closeAddModal();
                }
            }

            if (editModal && !editModal.classList.contains('hidden')) {
                if (event.target === editModal) {
                    closeEditModal();
                }
            }
        });

        // Auto-hide message alert after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('message-alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    </script>
</body>

</html>