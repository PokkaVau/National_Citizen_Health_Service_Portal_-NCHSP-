<?php
require 'config/db.php';

try {
    // Add missing columns
    $sql = "ALTER TABLE users 
            ADD COLUMN weight DECIMAL(5,2) NULL AFTER password,
            ADD COLUMN height DECIMAL(5,2) NULL AFTER weight,
            ADD COLUMN blood_type VARCHAR(5) NULL AFTER height";

    $pdo->exec($sql);
    echo "Successfully added weight, height, and blood_type columns to users table.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Column already exists or duplicate column name
        echo "Columns might already exist: " . $e->getMessage();
    } else {
        echo "Error adding columns: " . $e->getMessage();
    }
}
?>