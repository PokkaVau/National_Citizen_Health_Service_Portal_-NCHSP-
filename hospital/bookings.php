<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once 'auth_rep.php';

$hospital_id = $_SESSION['hospital_id'];
$message = "";
$error = "";

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Fetch Booking Details
    $stmt = $pdo->prepare("SELECT * FROM blood_bookings WHERE id = ? AND hospital_id = ?");
    $stmt->execute([$booking_id, $hospital_id]);
    $booking = $stmt->fetch();

    if ($booking) {
        if ($status == 'approved' && $booking['status'] != 'approved') {
            // Check Inventory
            $invStmt = $pdo->prepare("SELECT quantity FROM hospital_inventory WHERE hospital_id = ? AND blood_group = ?");
            $invStmt->execute([$hospital_id, $booking['blood_group']]);
            $stock = $invStmt->fetchColumn();

            if ($stock >= $booking['units']) {
                // Deduct Stock
                $updateInv = $pdo->prepare("UPDATE hospital_inventory SET quantity = quantity - ? WHERE hospital_id = ? AND blood_group = ?");
                $updateInv->execute([$booking['units'], $hospital_id, $booking['blood_group']]);

                // Update Status
                $updateStatus = $pdo->prepare("UPDATE blood_bookings SET status = 'approved' WHERE id = ?");
                $updateStatus->execute([$booking_id]);

                $message = "Booking approved and inventory updated.";
            } else {
                $error = "Insufficient stock to approve this booking.";
            }
        } elseif ($status == 'rejected') {
            // If previously approved, could restore stock? For simplicity, assume only pending -> rejected or pending -> approved.
            // But if we reject an ALREADY approved request, we should restore stock.
            if ($booking['status'] == 'approved') {
                // Restore Stock (Optional complexity, staying simple for now or adding if needed)
                // Let's implement restore for robustness
                $updateInv = $pdo->prepare("UPDATE hospital_inventory SET quantity = quantity + ? WHERE hospital_id = ? AND blood_group = ?");
                $updateInv->execute([$booking['units'], $hospital_id, $booking['blood_group']]);
            }

            $updateStatus = $pdo->prepare("UPDATE blood_bookings SET status = 'rejected' WHERE id = ?");
            $updateStatus->execute([$booking_id]);
            $message = "Booking rejected.";
        }
    }
}

// Fetch Bookings
$stmt = $pdo->prepare("
    SELECT bb.*, u.name as user_name, u.mobile as user_mobile 
    FROM blood_bookings bb 
    JOIN users u ON bb.user_id = u.id 
    WHERE bb.hospital_id = ? 
    ORDER BY bb.created_at DESC
");
$stmt->execute([$hospital_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Hospital Portal</title>
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
                        class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fas fa-boxes w-5"></i> Inventory
                    </a>
                    <a href="bookings.php"
                        class="flex items-center gap-3 px-4 py-3 bg-red-800 text-white rounded-lg transition-colors">
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
                        <h1 class="text-2xl font-bold text-slate-900">Bookings Management</h1>
                        <p class="text-slate-500">Approve or reject blood requests</p>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">User</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Request</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Contact</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($bookings as $b):
                                $status_color = match ($b['status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'fulfilled' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">
                                            <?php echo htmlspecialchars($b['user_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">
                                                <?php echo $b['blood_group']; ?>
                                            </span>
                                            <span class="text-sm text-slate-600">x
                                                <?php echo $b['units']; ?> units
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1">
                                            <?php echo date('M d, Y h:i A', strtotime($b['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-mono text-sm">
                                        <?php echo htmlspecialchars($b['user_mobile']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                            <?php echo ucfirst($b['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" class="flex justify-end gap-2">
                                            <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                            <?php if ($b['status'] == 'pending'): ?>
                                                <button type="submit" name="status" value="approved"
                                                    class="p-1 text-green-600 hover:bg-green-50 rounded"><i
                                                        class="fas fa-check"></i></button>
                                                <button type="submit" name="status" value="rejected"
                                                    class="p-1 text-red-600 hover:bg-red-50 rounded"><i
                                                        class="fas fa-times"></i></button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>