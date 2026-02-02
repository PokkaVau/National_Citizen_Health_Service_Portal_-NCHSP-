<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once 'auth_rep.php';

$hospital_id = $_SESSION['hospital_id'];
$message = "";

// Handle Inventory Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_inventory'])) {
    $blood_group = $_POST['blood_group'];
    $quantity = (int) $_POST['quantity'];

    try {
        // Upsert inventory
        $stmt = $pdo->prepare("
            INSERT INTO hospital_inventory (hospital_id, blood_group, quantity) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = ?
        ");
        $stmt->execute([$hospital_id, $blood_group, $quantity, $quantity]);
        $message = "Inventory updated successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch Current Inventory
// Fetch Current Inventory
$inventory = $pdo->query("SELECT blood_group, quantity FROM hospital_inventory WHERE hospital_id = $hospital_id")->fetchAll(PDO::FETCH_KEY_PAIR);
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory - Hospital Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hospital text-white"></i>
                    </div>
                    <span class="text-lg font-bold">Hospital Portal</span>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-home w-5"></i> Dashboard
                    </a>
                    <a href="inventory.php"
                        class="flex items-center gap-3 px-4 py-3 bg-red-800 text-white rounded-lg transition-colors">
                        <i class="fas fa-boxes w-5"></i> Inventory
                    </a>
                    <a href="bookings.php"
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-file-medical w-5"></i> Bookings
                    </a>
                    <a href="../logout.php"
                        class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-slate-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt w-5"></i> Logout
                    </a>
                </nav>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-slate-50 p-8">
            <div class="max-w-7xl mx-auto space-y-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Blood Inventory</h1>
                        <p class="text-slate-500">Update available blood bags</p>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($blood_groups as $bg):
                        $qty = isset($inventory[$bg]) ? $inventory[$bg] : 0;
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-red-600">
                                    <?php echo $bg; ?>
                                </span>
                                <i class="fas fa-tint text-red-100 text-3xl"></i>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="update_inventory" value="1">
                                <input type="hidden" name="blood_group" value="<?php echo $bg; ?>">
                                <label class="block text-xs font-medium text-slate-500 mb-1">Available Units</label>
                                <div class="flex gap-2">
                                    <input type="number" name="quantity" value="<?php echo $qty; ?>" min="0"
                                        class="flex-1 rounded-lg border-slate-300 focus:ring-red-500 focus:border-red-500 text-sm">
                                    <button type="submit"
                                        class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 text-sm">Save</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>