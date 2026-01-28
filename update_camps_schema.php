<?php
require 'config/db.php';

try {
    $pdo->exec("ALTER TABLE health_camps ADD COLUMN image_path VARCHAR(255) DEFAULT NULL");
    echo "Column 'image_path' added successfully to 'health_camps' table.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Duplicate column name
        echo "Column 'image_path' already exists.\n";
    } else {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
}
?>