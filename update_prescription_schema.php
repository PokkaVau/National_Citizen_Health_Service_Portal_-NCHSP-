<?php
require('config/db.php');

try {
    // Create Prescriptions Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS prescriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        doctor_id INT NOT NULL,
        appointment_id INT DEFAULT NULL,
        diagnosis TEXT,
        prescription_text TEXT,
        prescription_file VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
    )");
    echo "Created prescriptions table.\n";

    // Update Medications to link to doctor (optional but good)
    // For now we won't break existing structure, just treat medications as a list.

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>