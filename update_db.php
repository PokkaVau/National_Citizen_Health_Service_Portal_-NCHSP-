<?php
require('config/db.php');

try {
    // 1. Update admins table to include 'doctor' in enum if not already compatible (MySQL enums are strict, but we can just alter it)
    // Actually, 'role' enum in 'admins' table is: enum('super_admin','medical_officer'). We need to add 'doctor'.
    $pdo->exec("ALTER TABLE admins MODIFY COLUMN role ENUM('super_admin', 'medical_officer', 'doctor') NOT NULL DEFAULT 'doctor'");
    echo "Updated admins table enum.\n";

    // 2. Create doctors table
    $pdo->exec("CREATE TABLE IF NOT EXISTS doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        specialization VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    )");
    echo "Created doctors table.\n";

    // 3. Create appointments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        doctor_id INT NOT NULL,
        appointment_date DATETIME NOT NULL,
        description TEXT,
        status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
    )");
    echo "Created appointments table.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>