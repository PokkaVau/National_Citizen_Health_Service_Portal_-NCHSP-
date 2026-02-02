<?php
// Disable Caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Configure session timeout to 24 hours
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);

session_start();

// Function to check if user is logged in
function check_user_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Function to check if admin is logged in
function check_admin_login()
{
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Function to check if doctor is logged in
function check_doctor_login()
{
    if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] !== 'doctor' && $_SESSION['admin_role'] !== 'assistant')) {
        header("Location: ../login.php");
        exit();
    }
}
?>