<?php
require('config/db.php');

try {
    // 1. Create doctor_schedules table
    $pdo->exec("CREATE TABLE IF NOT EXISTS doctor_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT NOT NULL,
        available_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        is_booked BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
    )");
    echo "Created doctor_schedules table.\n";

    // 2. Add 'bio' and 'profile_picture' to doctors table if not exist
    $stmt = $pdo->query("SHOW COLUMNS FROM doctors LIKE 'bio'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE doctors ADD COLUMN bio TEXT AFTER specialization");
        echo "Added 'bio' column to doctors.\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM doctors LIKE 'profile_picture'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE doctors ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER bio");
        echo "Added 'profile_picture' column to doctors.\n";
    }

    // 3. Add 'schedule_id' to appointments table to link specific slot
    $stmt = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'schedule_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE appointments ADD COLUMN schedule_id INT DEFAULT NULL AFTER doctor_id");
        $pdo->exec("ALTER TABLE appointments ADD FOREIGN KEY (schedule_id) REFERENCES doctor_schedules(id) ON DELETE SET NULL");
        echo "Added 'schedule_id' to appointments.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>