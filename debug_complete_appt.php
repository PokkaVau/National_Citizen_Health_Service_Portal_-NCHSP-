<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];

// 1. Get the most recent pending or confirmed appointment
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? AND status IN ('pending', 'confirmed') ORDER BY appointment_date DESC LIMIT 1");
$stmt->execute([$user_id]);
$appt = $stmt->fetch();

if ($appt) {
    // 2. Mark it as completed
    $update = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
    $update->execute([$appt['id']]);
    echo "<h1>Success!</h1>";
    echo "<p>Appointment ID " . $appt['id'] . " with Doctor ID " . $appt['doctor_id'] . " has been marked as <strong>COMPLETED</strong>.</p>";
    echo "<p>Go back to <a href='dashboard.php'>Dashboard</a> to see the 'Rate Review' button.</p>";
} else {
    // Check if there are ALREADY completed appointments
    $check = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? AND status = 'completed'");
    $check->execute([$user_id]);
    $completed = $check->fetchAll();

    if (count($completed) > 0) {
        echo "<h1>Info</h1>";
        echo "<p>You already have " . count($completed) . " completed appointments.</p>";
        echo "<p>If you don't see the specific review button on the dashboard, it might be because you already reviewed them?</p>";

        // Check for reviews
        foreach ($completed as $c) {
            $r_stmt = $pdo->prepare("SELECT * FROM doctor_reviews WHERE appointment_id = ?");
            $r_stmt->execute([$c['id']]);
            $rev = $r_stmt->fetch();
            echo "Appt ID " . $c['id'] . ": " . ($rev ? "Reviewed" : "Not Reviewed") . "<br>";
        }
        echo "<p>Go back to <a href='dashboard.php'>Dashboard</a>.</p>";
    } else {
        echo "<h1>No Appointments</h1>";
        echo "<p>You don't have any appointments to mark as complete. Please book one first.</p>";
        echo "<a href='book_appointment.php'>Book Appointment</a>";
    }
}
?>