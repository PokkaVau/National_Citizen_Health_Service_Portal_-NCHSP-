<?php
require('config/db.php');

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER blood_type");
    echo "Added profile_picture column to users table.\n";
} catch (PDOException $e) {
    echo "Error (might already exist): " . $e->getMessage() . "\n";
}
?>