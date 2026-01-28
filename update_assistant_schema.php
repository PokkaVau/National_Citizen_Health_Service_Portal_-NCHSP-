<?php
require('config/db.php');

try {
    // 1. Update admins role enum
    $pdo->exec("ALTER TABLE admins MODIFY COLUMN role ENUM('super_admin', 'medical_officer', 'doctor', 'assistant') NOT NULL DEFAULT 'doctor'");
    echo "Updated admins table role enum to include 'assistant'.\n";

    // 2. Create assistants table
    $pdo->exec("CREATE TABLE IF NOT EXISTS assistants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        doctor_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
    )");
    echo "Created assistants table.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>