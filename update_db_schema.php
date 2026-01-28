<?php
require 'config/db.php';

try {
    // 1. Create doctor_schedules table
    $sql = "CREATE TABLE IF NOT EXISTS `doctor_schedules` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `doctor_id` int(11) NOT NULL,
        `available_date` date NOT NULL,
        `start_time` time NOT NULL,
        `end_time` time NOT NULL,
        `is_booked` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `doctor_id` (`doctor_id`),
        CONSTRAINT `doctor_schedules_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $pdo->exec($sql);
    echo "Table 'doctor_schedules' created or already exists.\n";

    // 2. Add schedule_id to appointments table
    // Check if column exists first
    $stmt = $pdo->query("SHOW COLUMNS FROM `appointments` LIKE 'schedule_id'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $sql = "ALTER TABLE `appointments` ADD `schedule_id` int(11) DEFAULT NULL AFTER `doctor_id`";
        $pdo->exec($sql);
        echo "Column 'schedule_id' added to 'appointments' table.\n";

        // Add FK
        $sql = "ALTER TABLE `appointments` ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`schedule_id`) REFERENCES `doctor_schedules` (`id`) ON DELETE SET NULL";
        $pdo->exec($sql);
        echo "Foreign key constraint added for 'schedule_id'.\n";
    } else {
        echo "Column 'schedule_id' already exists in 'appointments' table.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>