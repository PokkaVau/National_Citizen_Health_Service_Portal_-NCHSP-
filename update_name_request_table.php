<?php
require_once 'config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS doctor_name_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT NOT NULL,
        current_name VARCHAR(100) NOT NULL,
        requested_name VARCHAR(100) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'doctor_name_requests' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>