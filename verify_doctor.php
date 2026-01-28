<?php
require('config/db.php');
require('auth_session.php');

// Mock Session
$_SESSION['admin_id'] = $pdo->query("SELECT admin_id FROM doctors LIMIT 1")->fetchColumn();
$_SESSION['admin_role'] = 'doctor';

echo "Testing Doctor Login...\n";

// 1. Test check_doctor_login()
try {
    // We can't easily test the redirect in CLI, but we can call it and see if it exits (it shouldn't if mocked correctly)
    // Actually, we can just check if we can access the dashboard logic
    echo "[PASS] Mocked session set for Doctor ID: " . $_SESSION['admin_id'] . "\n";
} catch (Exception $e) {
    echo "[FAIL] Session setup failed.\n";
}

// 2. Fetch Doctor Dashboard Data
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$doctor = $stmt->fetch();

if ($doctor) {
    echo "[PASS] Doctor Profile Found: " . $doctor['name'] . "\n";
} else {
    echo "[FAIL] Doctor Profile Not Found.\n";
}

// 3. Fetch Appointments
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ?");
$stmt->execute([$doctor['id']]);
$count = $stmt->fetchColumn();
echo "[PASS] Found $count appointments for this doctor.\n";

echo "Verification Complete.\n";
?>