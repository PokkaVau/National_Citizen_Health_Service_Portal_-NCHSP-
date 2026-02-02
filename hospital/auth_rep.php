<?php
// htdocs/dbms/hospital/auth_rep.php
// Helper to verify if user is a representative
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for admin_id set during login for 'hospital_rep' role
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Optional: Double check role if needed, but session should be enough for basic check.
// We can re-verify from DB if strict security is needed.
require_once __DIR__ . '/../config/db.php';

$admin_id = $_SESSION['admin_id'];

// Verify connection in hospital_representatives
$stmt = $pdo->prepare("SELECT * FROM hospital_representatives WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$rep = $stmt->fetch();

if (!$rep) {
    // Not a representative or not assigned
    header("Location: ../login.php?error=unauthorized_rep");
    exit();
}

// Refresh Session Data ensuring validity
$_SESSION['hospital_id'] = $rep['hospital_id'];
$_SESSION['rep_id'] = $rep['id'];
?>