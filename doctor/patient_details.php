<?php
require('../config/db.php');
require('../auth_session.php');
check_doctor_login();

$admin_id = $_SESSION['admin_id'];
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// Get Doctor Context
if ($_SESSION['admin_role'] == 'assistant') {
    $stmt = $pdo->prepare("SELECT d.* FROM doctors d JOIN assistants a ON d.id = a.doctor_id WHERE a.admin_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE admin_id = ?");
}
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

if (!$user_id) {
    header("Location: dashboard.php");
    exit();
}

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

// Handle Add Medicine
if (isset($_POST['add_medicine'])) {
    $med_name = $_POST['med_name'];
    $dosage = $_POST['dosage'];
    $frequency = $_POST['frequency'];
    $total_capsules = $_POST['total_capsules'];

    $stmt = $pdo->prepare("INSERT INTO medications (user_id, name, dosage, frequency, capsules_left, total_capsules, color_class) VALUES (?, ?, ?, ?, ?, ?, 'blue')");
    // Capsules left starts full
    if ($stmt->execute([$user_id, $med_name, $dosage, $frequency, $total_capsules, $total_capsules])) {
        $message = "Medicine added to patient profile.";
    } else {
        $error = "Failed to add medicine.";
    }
}

// Handle Add Prescription (Digital Note)
if (isset($_POST['add_prescription'])) {
    $diagnosis = $_POST['diagnosis'];
    $prescription_text = $_POST['prescription_text'];

    // Optional File
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
    if ($stmt->execute([$user_id, $doctor_id, $diagnosis, $prescription_text, $file_path])) {
        $message = "Prescription saved.";
    } else {
        $error = "Failed to save prescription.";
    }
}

// Fetch Data for View
$reports = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY test_date DESC");
$reports->execute([$user_id]);
$reports = $reports->fetchAll();

$medications = $pdo->prepare("SELECT * FROM medications WHERE user_id = ?");
$medications->execute([$user_id]);
$medications = $medications->fetchAll();

$prescriptions = $pdo->prepare("SELECT * FROM prescriptions WHERE user_id = ? ORDER BY created_at DESC");
$prescriptions->execute([$user_id]);
$prescriptions = $prescriptions->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management - Doctor Portal</title>
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

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-7xl mx-auto h-screen overflow-y-auto">
        <div class="p-8 pb-20">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Patient Details</h1>
                <a href="dashboard.php"
                    class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-2">
                    <span class="material-symbols-outlined">arrow_back</span> Back to Dashboard
                </a>
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

            <!-- Patient Header -->
            <div
                class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100 mb-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="size-16 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center overflow-hidden">
                        <?php if ($patient['profile_picture']): ?>
                            <img src="<?php echo htmlspecialchars(str_replace('../', '', $patient['profile_picture'])); ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="material-symbols-outlined text-3xl">person</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                                <?php echo htmlspecialchars($patient['name']); ?>
                        </h1>
                        <div class="flex items-center gap-4 text-sm text-slate-500 mt-1">
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-base">call</span>
                                   <?php echo $patient['mobile']; ?>
                            </span>
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-base">bloodtype</span>
                                   <?php echo $patient['blood_type']; ?>
                            </span>
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-base">straighten</span>
                                   <?php echo $patient['height']; ?>cm
                            </span>
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-base">monitor_weight</span>
                                    <?php echo $patient['weight']; ?>kg
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Col: Prescriptions & Meds -->
                <div class="space-y-8">
                    <!-- Add Prescription -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">prescriptions</span> Add
                            Prescription /
                            Note
                        </h2>
                        <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <input type="text" name="diagnosis" placeholder="Diagnosis (e.g. Viral Fever)" required
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <textarea name="prescription_text" rows="3" placeholder="Prescription details, notes..."
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm text-slate-500 mb-1">Upload File (PDF/Image) -
                                    Optional</label>
                                <input type="file" name="presc_file" class="text-sm">
                            </div>
                            <button type="submit" name="add_prescription"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg transition-colors">
                                Save Prescription
                            </button>
                        </form>
                    </div>

                    <!-- Add Medicine -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">pill</span> Add Medication
                        </h2>
                        <form action="" method="post" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="med_name" placeholder="Medicine Name" required
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                                <input type="text" name="dosage" placeholder="Dosage (e.g. 500mg)" required
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="frequency" placeholder="Freq (e.g. 1-0-1)" required
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                                <input type="number" name="total_capsules" placeholder="Total Count" required
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                            </div>
                            <button type="submit" name="add_medicine"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-lg transition-colors">
                                Add Medicine
                            </button>
                        </form>
                    </div>

                    <!-- Previous Prescriptions List -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100">
                        <h3 class="font-bold text-slate-800 mb-4">History</h3>
                        <div class="space-y-4">
                             <?php foreach ($prescriptions as $presc): ?>
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-slate-800">
                                             <?php echo htmlspecialchars($presc['diagnosis']); ?>
                                        </h4>
                                        <span class="text-xs text-slate-400">
                                               <?php echo date('M d, Y', strtotime($presc['created_at'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 mt-1">
                                            <?php echo nl2br(htmlspecialchars($presc['prescription_text'])); ?>
                                    </p>
                                      <?php if ($presc['prescription_file']): ?>
                                        <a href="<?php echo htmlspecialchars($presc['prescription_file']); ?>" target="_blank"
                                            class="text-xs text-indigo-600 hover:underline mt-2 inline-block">View File</a>
                                       <?php endif; ?>
                                </div>
                             <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Col: Reports -->
                <div class="space-y-8">
                    <!-- Upload Report -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">upload_file</span> Upload Medical
                            Report
                        </h2>
                        <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <input type="text" name="test_name" placeholder="Test Name (e.g. Blood Count)" required
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="result_value" placeholder="Result (e.g. Normal)"
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                                <input type="date" name="test_date" required value="<?php echo date('Y-m-d'); ?>"
                                    class="w-full px-3 py-2 border rounded-lg outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm text-slate-500 mb-1">Report File (PDF/Image)</label>
                                <input type="file" name="report_file" class="text-sm" required>
                            </div>
                            <button type="submit" name="upload_report"
                                class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 rounded-lg transition-colors">
                                Upload Report
                            </button>
                        </form>
                    </div>

                    <!-- Reports List -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100">
                        <h3 class="font-bold text-slate-800 mb-4">Patient Reports</h3>
                        <div class="space-y-3">
                              <?php foreach ($reports as $rep): ?>
                                <div
                                    class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm">
                                             <?php echo htmlspecialchars($rep['test_name']); ?>
                                        </p>
                                        <p class="text-xs text-slate-500">
                                               <?php echo date('M d, Y', strtotime($rep['test_date'])); ?> •
                                              <?php echo htmlspecialchars($rep['result_value']); ?>
                                        </p>
                                    </div>
                                       <?php if ($rep['report_file']): ?>
                                        <a href="<?php echo htmlspecialchars($rep['report_file']); ?>" target="_blank"
                                            class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </a>
                                       <?php endif; ?>
                                </div>
                             <?php endforeach; ?>
                            <?php if (count($reports) == 0): ?>
                                <p class="text-sm text-slate-400">No reports found.</p>
                              <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>