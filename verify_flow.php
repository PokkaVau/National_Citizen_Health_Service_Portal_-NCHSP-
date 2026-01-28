<?php
require('config/db.php');

echo "Starting Verification...\n";

// 1. Create a Test Admin/Doctor
try {
    $username = "test_doc_" . time();
    $password = password_hash("123", PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO admins (username, password, role) VALUES ('$username', '$password', 'doctor')");
    $admin_id = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO doctors (admin_id, name, specialization) VALUES ($admin_id, 'Dr. Test', 'General')");
    $doctor_id = $pdo->lastInsertId();
    echo "[PASS] Created Doctor (ID: $doctor_id)\n";
} catch (Exception $e) {
    echo "[FAIL] Creating Doctor: " . $e->getMessage() . "\n";
    exit;
}

// 2. Create a Test User (if not exists, or just pick first one)
$user_id = $pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
if (!$user_id) {
    echo "[FAIL] No users found to test appointment.\n";
    exit;
}

// 3. Book Appointment
try {
    $date = date('Y-m-d H:i:s', strtotime('+1 day'));
    $pdo->exec("INSERT INTO appointments (user_id, doctor_id, appointment_date, description) VALUES ($user_id, $doctor_id, '$date', 'Test Checkup')");
    echo "[PASS] Booked Appointment\n";
} catch (Exception $e) {
    echo "[FAIL] Booking Appointment: " . $e->getMessage() . "\n";
}

// 4. Verify Data Linkage
$appt = $pdo->query("SELECT a.*, d.name as doc_name, u.name as user_name FROM appointments a JOIN doctors d ON a.doctor_id = d.id JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 1")->fetch();

if ($appt && $appt['doc_name'] === 'Dr. Test') {
    echo "[PASS] Verified Appointment Linkage: {$appt['user_name']} has appointment with {$appt['doc_name']}\n";
} else {
    echo "[FAIL] Data Linkage Verification Failed\n";
}

echo "Verification Complete.\n";
?>