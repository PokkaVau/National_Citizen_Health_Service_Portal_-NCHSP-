<?php
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