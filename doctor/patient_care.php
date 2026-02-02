<?php
require_once('../config/db.php');
require_once('../auth_session.php');
check_doctor_login();

$admin_id = $_SESSION['admin_id'];
$user_id = $_GET['user_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');
$appt_id = $_GET['appt_id'] ?? null;

if (!$user_id) {
    header("Location: checkups.php");
    exit();
}

// Get Doctor Info
if ($_SESSION['admin_role'] == 'assistant') {
    $stmt = $pdo->prepare("SELECT d.* FROM doctors d JOIN assistants a ON d.id = a.doctor_id WHERE a.admin_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE admin_id = ?");
}
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();

// Fetch Patient Info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$patient = $stmt->fetch();

$message = "";
$error = "";

// Handle Report Upload
if (isset($_POST['upload_report'])) {
    $test_name = $_POST['test_name'];
    $result_value = $_POST['result_value'];
    $test_date = $_POST['test_date'];

    // File Upload
    $file_path = "";
    if (isset($_FILES['report_file']) && $_FILES['report_file']['size'] > 0) {
        $upload_dir = '../uploads/reports/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['report_file']['name'], PATHINFO_EXTENSION);
        $filename = 'rep_' . $user_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target)) {
            $file_path = $target;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO reports (user_id, test_name, test_date, result_value, report_file, doctor_name) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $test_name, $test_date, $result_value, $file_path, $doctor['name']])) {
        $message = "Report uploaded successfully.";
    } else {
        $error = "Failed to upload report.";
    }
}

// Handle Prescription
if (isset($_POST['add_prescription'])) {
    $diagnosis = $_POST['diagnosis'];
    $prescription_text = $_POST['prescription_text'];

    $file_path = "";
    if (isset($_FILES['presc_file']) && $_FILES['presc_file']['size'] > 0) {
        $upload_dir = '../uploads/prescriptions/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['presc_file']['name'], PATHINFO_EXTENSION);
        $filename = 'presc_' . $user_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['presc_file']['tmp_name'], $target)) {
            $file_path = $target;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO prescriptions (user_id, doctor_id, diagnosis, prescription_text, prescription_file) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $doctor['id'], $diagnosis, $prescription_text, $file_path])) {
        $message = "Prescription saved.";
    } else {
        $error = "Failed to save prescription.";
    }
}

// Handle Medicine
if (isset($_POST['add_medicine'])) {
    $med_name = $_POST['med_name'];
    $dosage = $_POST['dosage'];
    $frequency = $_POST['frequency'];
    $total_capsules = $_POST['total_capsules'];

    $stmt = $pdo->prepare("INSERT INTO medications (user_id, name, dosage, frequency, capsules_left, total_capsules, color_class) VALUES (?, ?, ?, ?, ?, ?, 'blue')");
    if ($stmt->execute([$user_id, $med_name, $dosage, $frequency, $total_capsules, $total_capsules])) {
        $message = "Medicine added.";
    } else {
        $error = "Failed to add medicine.";
    }
}

// Fetch Records
$reports = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY test_date DESC");
$reports->execute([$user_id]);
$reports = $reports->fetchAll();

$prescriptions = $pdo->prepare("SELECT * FROM prescriptions WHERE user_id = ? ORDER BY created_at DESC");
$prescriptions->execute([$user_id]);
$prescriptions = $prescriptions->fetchAll();

$medications = $pdo->prepare("SELECT * FROM medications WHERE user_id = ? ORDER BY id DESC");
$medications->execute([$user_id]);
$medications = $medications->fetchAll();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'reports';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Care - Doctor Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 flex font-inter">
    <?php include 'layout/sidebar.php'; ?>
    <main class="flex-1 w-full max-w-7xl mx-auto h-screen overflow-y-auto p-8">
        <!-- Header -->
        <header class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-4">
                <a href="checkups.php?date=<?php echo $date; ?>"
                    class="p-2 bg-white rounded-lg border border-gray-200 text-slate-600 hover:text-indigo-600">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        <?php echo htmlspecialchars($patient['name']); ?>
                    </h1>
                    <div class="flex gap-4 text-sm text-slate-500 mt-1">
                        <span>
                            <?php 
                                $age = date_diff(date_create($patient['dob']), date_create('today'))->y;
                                echo $age . " Years"; 
                            ?>
                        </span>
                        <span>•</span>
                        <span>
                            <?php echo $patient['blood_type']; ?>
                        </span>
                        <span>•</span>
                        <span>
                            <?php echo $patient['mobile']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($appt_id): ?>
                <form action="dashboard.php" method="post">
                    <input type="hidden" name="appointment_id" value="<?php echo $appt_id; ?>">
                    <button type="submit" name="update_status" value="completed"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium">
                        <span class="material-symbols-outlined">check_circle</span>
                        Complete Visit
                    </button>
                </form>
            <?php endif; ?>
        </header>

        <?php if ($message): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-8">
            <nav class="flex gap-8" aria-label="Tabs">
                <a href="?user_id=<?php echo $user_id; ?>&date=<?php echo $date; ?>&appt_id=<?php echo $appt_id; ?>&tab=reports"
                    class="<?php echo $active_tab == 'reports' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined">lab_panel</span> Reports
                </a>
                <a href="?user_id=<?php echo $user_id; ?>&date=<?php echo $date; ?>&appt_id=<?php echo $appt_id; ?>&tab=prescriptions"
                    class="<?php echo $active_tab == 'prescriptions' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined">prescriptions</span> Prescriptions
                </a>
                <a href="?user_id=<?php echo $user_id; ?>&date=<?php echo $date; ?>&appt_id=<?php echo $appt_id; ?>&tab=medicine"
                    class="<?php echo $active_tab == 'medicine' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined">pill</span> Medicine
                </a>
            </nav>
        </div>

        <!-- Content -->

        <!-- Reports Tab -->
        <?php if ($active_tab == 'reports'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-slate-800 mb-4">Upload New Report</h3>
                        <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
                            <input type="text" name="test_name" placeholder="Test Name" required
                                class="w-full px-3 py-2 border rounded-lg">
                            <input type="text" name="result_value" placeholder="Result Value" required
                                class="w-full px-3 py-2 border rounded-lg">
                            <input type="date" name="test_date" required value="<?php echo date('Y-m-d'); ?>"
                                class="w-full px-3 py-2 border rounded-lg">
                            <input type="file" name="report_file" required class="w-full text-sm">
                            <button type="submit" name="upload_report"
                                class="w-full bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700">Upload
                                Report</button>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($reports as $rep): ?>
                        <div class="bg-white p-4 rounded-xl border border-gray-200 flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-slate-800">
                                    <?php echo htmlspecialchars($rep['test_name']); ?>
                                </h4>
                                <p class="text-sm text-slate-500">
                                    <?php echo date('M d, Y', strtotime($rep['test_date'])); ?> •
                                    <?php echo htmlspecialchars($rep['result_value']); ?>
                                </p>
                            </div>
                            <?php if ($rep['report_file']): ?>
                                <a href="<?php echo htmlspecialchars($rep['report_file']); ?>" target="_blank"
                                    class="text-indigo-600 hover:underline text-sm font-medium">View PDF</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Prescription Tab -->
        <?php if ($active_tab == 'prescriptions'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-slate-800 mb-4">New Prescription</h3>
                        <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
                            <input type="text" name="diagnosis" placeholder="Diagnosis" required
                                class="w-full px-3 py-2 border rounded-lg">
                            <textarea name="prescription_text" rows="4" placeholder="Rx details..."
                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                            <input type="file" name="presc_file" class="w-full text-sm">
                            <button type="submit" name="add_prescription"
                                class="w-full bg-purple-600 text-white py-2 rounded-lg font-medium hover:bg-purple-700">Save
                                Prescription</button>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($prescriptions as $p): ?>
                        <div class="bg-white p-4 rounded-xl border border-gray-200">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-bold text-slate-800">
                                    <?php echo htmlspecialchars($p['diagnosis']); ?>
                                </h4>
                                <span class="text-xs text-slate-500">
                                    <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 whitespace-pre-line">
                                <?php echo htmlspecialchars($p['prescription_text']); ?>
                            </p>
                            <?php if ($p['prescription_file']): ?>
                                <a href="<?php echo htmlspecialchars($p['prescription_file']); ?>" target="_blank"
                                    class="block mt-2 text-indigo-600 hover:underline text-sm font-medium">View Attachment</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Medicine Tab -->
        <?php if ($active_tab == 'medicine'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <h3 class="font-bold text-slate-800 mb-4">Add Medicine</h3>
                        <form action="" method="post" class="space-y-4">
                            <input type="text" name="med_name" placeholder="Medicine Name" required
                                class="w-full px-3 py-2 border rounded-lg">
                            <input type="text" name="dosage" placeholder="Dosage (e.g. 500mg)" required
                                class="w-full px-3 py-2 border rounded-lg">
                            <input type="text" name="frequency" placeholder="Frequency (e.g. 1-0-1)" required
                                class="w-full px-3 py-2 border rounded-lg">
                            <input type="number" name="total_capsules" placeholder="Total Count" required
                                class="w-full px-3 py-2 border rounded-lg">
                            <button type="submit" name="add_medicine"
                                class="w-full bg-emerald-600 text-white py-2 rounded-lg font-medium hover:bg-emerald-700">Add
                                Medicine</button>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-slate-500">
                                <tr>
                                    <th class="p-4">Medicine</th>
                                    <th class="p-4">Dosage</th>
                                    <th class="p-4">Freq</th>
                                    <th class="p-4">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($medications as $m): ?>
                                    <tr>
                                        <td class="p-4 font-medium text-slate-900">
                                            <?php echo htmlspecialchars($m['name']); ?>
                                        </td>
                                        <td class="p-4 text-slate-600">
                                            <?php echo htmlspecialchars($m['dosage']); ?>
                                        </td>
                                        <td class="p-4 text-slate-600">
                                            <?php echo htmlspecialchars($m['frequency']); ?>
                                        </td>
                                        <td class="p-4 text-slate-600">
                                            <?php echo htmlspecialchars($m['total_capsules']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>
</body>

</html>