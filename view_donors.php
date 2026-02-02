<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];
$request_id = isset($_GET['request_id']) ? $_GET['request_id'] : null;

if (!$request_id) {
    header("Location: dashboard.php");
    exit();
}

// 1. Validate Request Ownership and Status
$stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE id = ? AND user_id = ? AND status = 'approved'");
$stmt->execute([$request_id, $user_id]);
$request = $stmt->fetch();

if (!$request) {
    // Redirect if request not found, not owned by user, or not approved
    header("Location: dashboard.php?error=invalid_request");
    exit();
}

$request_id = $request['id']; // Ensure we have the integer ID
$required_blood_group = $request['blood_group'];

// Log Access
try {
    // Check if recently logged to prevent flooding (optional, but good practice specific to this session/request)
    // For now, simple insert on every page load as requested
    $logStmt = $pdo->prepare("INSERT INTO donor_access_logs (user_id, request_id) VALUES (?, ?)");
    $logStmt->execute([$user_id, $request_id]);
} catch (PDOException $e) {
    // Silent fail or log error, don't stop user execution
}

// 2. Fetch Matching Donors
// Note: We now query the dedicated 'donors' table.
$stmt = $pdo->prepare("SELECT donor_name as name, age, blood_group as blood_type, last_donation as last_donation_date, contact as mobile FROM donors WHERE blood_group = ?");
$stmt->execute([$required_blood_group]);
$donors = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matching Donors - NCHSP</title>
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <a href="dashboard.php"
                    class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors text-slate-600">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Matching Donors</h1>
                    <p class="text-slate-500">Donors matching blood group <span class="font-bold text-red-600">
                            <?php echo htmlspecialchars($required_blood_group); ?>
                        </span></p>
                </div>
            </div>
        </div>

        <!-- Donors List -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if (count($donors) > 0): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-4">Donor Name</th>
                            <th class="px-6 py-4">Blood Group</th>
                            <th class="px-6 py-4">Last Donation</th>
                            <th class="px-6 py-4">Age</th>
                            <th class="px-6 py-4">Contact</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($donors as $donor):
                            // Age is now directly available
                            $age = $donor['age'];
                            $last_donation = $donor['last_donation_date'] ? date('M d, Y', strtotime($donor['last_donation_date'])) : 'Never';
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <?php echo htmlspecialchars($donor['name']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-tint"></i>
                                        <?php echo htmlspecialchars($donor['blood_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <?php echo $last_donation; ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <?php echo $age; ?> years
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <?php echo htmlspecialchars($donor['mobile']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                        <i class="fas fa-users-slash text-slate-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900">No Donors Found</h3>
                    <p class="text-slate-500 mt-1">We couldn't find any donors with blood group
                        <?php echo htmlspecialchars($required_blood_group); ?> at the moment.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>