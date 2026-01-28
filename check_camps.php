<?php
require('config/db.php');
try {
    $result = $pdo->query("SELECT COUNT(*) FROM health_camps");
    echo "Health Table Exists. Count: " . $result->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'doesn\'t exist') !== false) {
        // Create table if not exists, though user didn't ask for it, it fixes the error.
        // Assuming minimal schema for health_camps based on 'manage_camps.php' context.
    }
}
?>