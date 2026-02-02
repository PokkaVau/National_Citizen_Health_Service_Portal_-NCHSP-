<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";
$error = "";

// Handle Add Representative
if (isset($_POST['add_rep'])) {
    $hospital_id = $_POST['hospital_id'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if username exists
    $check = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $check->execute([$username]);
    if ($check->rowCount() > 0) {
        $error = "Username already exists.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Create Admin Account
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'hospital_representative')");
            $stmt->execute([$username, $hashed]);
            $admin_id = $pdo->lastInsertId();

            // 2. Assign to Hospital
            $stmt = $pdo->prepare("INSERT INTO hospital_representatives (admin_id, hospital_id, assigned_by) VALUES (?, ?, ?)");
            $stmt->execute([$admin_id, $hospital_id, $_SESSION['admin_id']]);

            $pdo->commit();
            $message = "Representative created successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle Delete
if (isset($_POST['delete_rep'])) {
    $admin_id = $_POST['admin_id'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM hospital_representatives WHERE admin_id = ?")->execute([$admin_id]);
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$admin_id]);
        $pdo->commit();
        $message = "Representative deleted.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error deleting: " . $e->getMessage();
    }
}

// Fetch Representatives
$stmt = $pdo->query("
    SELECT hr.id, hr.admin_id, a.username, h.name as hospital_name, hr.created_at
    FROM hospital_representatives hr
    JOIN admins a ON hr.admin_id = a.id
    JOIN hospitals h ON hr.hospital_id = h.id
    WHERE a.role = 'hospital_representative'
    ORDER BY hr.created_at DESC
");
$representatives = $stmt->fetchAll();

// Fetch Hospitals
$hospitals = $pdo->query("SELECT id, name FROM hospitals ORDER BY name ASC")->fetchAll();

// Get statistics
$total_reps = count($representatives);
$active_hospitals = $pdo->query("SELECT COUNT(DISTINCT hospital_id) as count FROM hospital_representatives")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Representatives - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

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

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .card-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
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
                        <p class="text-xs text-slate-400 mt-1">Representative Management</p>
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
                        class="flex items-center gap-3 px-4 py-3.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-300 group">
                        <div
                            class="w-8 h-8 flex items-center justify-center bg-white/0 group-hover:bg-blue-500/20 rounded-lg transition-all">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <span class="font-medium">Hospitals</span>
                    </a>
                    <a href="manage_representatives.php"
                        class="flex items-center gap-3 px-4 py-3.5 bg-gradient-to-r from-blue-600/20 to-indigo-600/20 text-white border-l-4 border-blue-500 rounded-xl transition-all">
                        <div class="w-8 h-8 flex items-center justify-center bg-blue-500 rounded-lg">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="font-medium">Representatives</span>
                        <span
                            class="ml-auto bg-blue-500 text-xs px-2 py-1 rounded-full"><?php echo $total_reps; ?></span>
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
                                class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-user-tie text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-slate-900">Hospital Representatives</h1>
                                <p class="text-slate-500">Create and manage access for hospital staff accounts</p>
                            </div>
                        </div>
                    </div>
                    <button onclick="openAddModal()"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover-lift flex items-center gap-3 group">
                        <i class="fas fa-plus"></i>
                        <span class="font-semibold">Create Representative</span>
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover-lift">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-500 text-sm font-medium">Total Representatives</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo $total_reps; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <div class="flex items-center text-sm text-slate-500">
                                <span class="status-dot bg-green-500"></span>
                                <span>All accounts active</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover-lift">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-500 text-sm font-medium">Hospitals Covered</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo $active_hospitals; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-hospital text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-xs text-slate-500">With representative access</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover-lift">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-500 text-sm font-medium">Today's Activity</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2">0</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-chart-line text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 pulse"></span>
                                System Ready
                            </span>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div id="message-alert"
                        class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center justify-between slide-in">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-xl"></i>
                            <span><?php echo $message; ?></span>
                        </div>
                        <button onclick="document.getElementById('message-alert').style.display='none'"
                            class="text-white/80 hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div id="error-alert"
                        class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center justify-between slide-in">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                        <button onclick="document.getElementById('error-alert').style.display='none'"
                            class="text-white/80 hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Representatives Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover-lift">
                    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">Representative Directory</h2>
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <input type="text" placeholder="Search representatives..."
                                        class="pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all"
                                        onkeyup="filterTable(this.value)">
                                    <i
                                        class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                                </div>
                                <button onclick="openAddModal()"
                                    class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-4 py-2 rounded-lg transition-all duration-300 flex items-center gap-2 text-sm">
                                    <i class="fas fa-plus"></i>
                                    New
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-8 py-4 text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Representative</th>
                                    <th class="px-8 py-4 text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Hospital Assignment</th>
                                    <th class="px-8 py-4 text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-8 py-4 text-right text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($representatives as $rep): ?>
                                    <tr class="hover:bg-slate-50/80 transition-all duration-300"
                                        data-searchable="<?php echo htmlspecialchars(strtolower($rep['username'] . ' ' . $rep['hospital_name'])); ?>">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-12 h-12 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                                                    <i class="fas fa-user-tie text-purple-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-slate-900 text-lg">
                                                        @<?php echo htmlspecialchars($rep['username']); ?>
                                                    </div>
                                                    <div class="text-sm text-slate-500 mt-1">
                                                        <i class="fas fa-id-card text-xs mr-2"></i>
                                                        ID: <?php echo htmlspecialchars($rep['admin_id']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-hospital text-blue-500"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-slate-900">
                                                        <?php echo htmlspecialchars($rep['hospital_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-400 mt-1">
                                                        <i class="fas fa-calendar text-xs mr-1"></i>
                                                        Added <?php echo date('M d, Y', strtotime($rep['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-2">
                                                <span class="status-dot bg-green-500"></span>
                                                <span class="font-medium text-green-700">Active</span>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                    <i class="fas fa-user-shield text-xs mr-1.5"></i>
                                                    Hospital Representative
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    onclick="viewRepresentativeDetails(<?php echo htmlspecialchars(json_encode($rep)); ?>)"
                                                    class="w-10 h-10 flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-lg transition-all duration-300 hover-lift"
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button
                                                    onclick="resetPassword(<?php echo $rep['admin_id']; ?>, '<?php echo htmlspecialchars($rep['username']); ?>')"
                                                    class="w-10 h-10 flex items-center justify-center bg-yellow-50 hover:bg-yellow-100 text-yellow-600 rounded-lg transition-all duration-300 hover-lift"
                                                    title="Reset Password">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirmDelete()" class="inline">
                                                    <input type="hidden" name="delete_rep" value="1">
                                                    <input type="hidden" name="admin_id"
                                                        value="<?php echo $rep['admin_id']; ?>">
                                                    <button type="submit"
                                                        class="w-10 h-10 flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-all duration-300 hover-lift"
                                                        title="Delete Representative">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($representatives)): ?>
                        <div class="text-center py-12">
                            <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-user-tie text-slate-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-700 mb-2">No Representatives Found</h3>
                            <p class="text-slate-500 mb-6">Create your first hospital representative account</p>
                            <button onclick="openAddModal()"
                                class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-lg transition-all duration-300 shadow hover:shadow-lg inline-flex items-center gap-2">
                                <i class="fas fa-plus"></i>
                                Create First Representative
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="px-8 py-4 border-t border-slate-200 bg-slate-50/50">
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <div class="flex items-center gap-4">
                                <span class="font-medium">Total: <?php echo $total_reps; ?> representatives</span>
                                <span class="text-slate-400">•</span>
                                <span>Across <?php echo $active_hospitals; ?> hospitals</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-shield-alt text-slate-400"></i>
                                <span>All accounts secured</span>
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
                        class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-plus text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Create Representative Account</h2>
                        <p class="text-sm text-slate-500">Assign hospital access credentials</p>
                    </div>
                </div>
            </div>
            <form method="POST">
                <input type="hidden" name="add_rep" value="1">
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Select Hospital *</label>
                        <div class="relative">
                            <i
                                class="fas fa-hospital absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <select name="hospital_id" required
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all appearance-none bg-white">
                                <option value="">Select a hospital...</option>
                                <?php foreach ($hospitals as $h): ?>
                                    <option value="<?php echo $h['id']; ?>"><?php echo htmlspecialchars($h['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Username *</label>
                        <div class="relative">
                            <i
                                class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="username" required placeholder="Enter username"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all">
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Username must be unique</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Password *</label>
                        <div class="relative">
                            <i
                                class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password" required placeholder="Enter password"
                                class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all">
                            <button type="button" onclick="togglePassword(this)"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Minimum 8 characters recommended</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 rounded-b-2xl">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeAddModal()"
                            class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-300 font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-300 shadow hover:shadow-lg font-medium">
                            <i class="fas fa-save mr-2"></i>Create Account
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

        function confirmDelete() {
            return confirm('Are you sure you want to delete this representative? This will permanently remove their access.');
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

        function viewRepresentativeDetails(rep) {
            alert(`Representative Details:\n\nUsername: @${rep.username}\nID: ${rep.admin_id}\nHospital: ${rep.hospital_name}\nCreated: ${new Date(rep.created_at).toLocaleDateString()}\n\nRole: Hospital Representative`);
        }

        function resetPassword(id, username) {
            if (confirm(`Reset password for @${username}?`)) {
                const newPassword = prompt(`Enter new password for @${username}:`, '');
                if (newPassword && newPassword.length >= 6) {
                    // In a real implementation, this would make an AJAX call to reset the password
                    alert('Password reset feature would be implemented here.\nFor now, create a new representative or use the database directly.');
                } else if (newPassword !== null) {
                    alert('Password must be at least 6 characters long.');
                }
            }
        }

        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (event) {
            const addModal = document.getElementById('addModal');
            if (addModal && !addModal.classList.contains('hidden') && event.target === addModal) {
                closeAddModal();
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const messageAlert = document.getElementById('message-alert');
            const errorAlert = document.getElementById('error-alert');

            if (messageAlert) {
                messageAlert.style.opacity = '0';
                setTimeout(() => messageAlert.remove(), 300);
            }

            if (errorAlert) {
                errorAlert.style.opacity = '0';
                setTimeout(() => errorAlert.remove(), 300);
            }
        }, 5000);

        // Focus on first input when modal opens
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('addModal');
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (!modal.classList.contains('hidden')) {
                        const firstInput = modal.querySelector('select[name="hospital_id"]');
                        if (firstInput) firstInput.focus();
                    }
                });
            });

            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
</body>

</html>