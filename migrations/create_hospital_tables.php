<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. Create hospitals table
    $sql = "CREATE TABLE IF NOT EXISTS hospitals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        contact_number VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Table 'hospitals' created successfully.<br>";

    // 2. Create hospital_inventory table
    $sql = "CREATE TABLE IF NOT EXISTS hospital_inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_id INT NOT NULL,
        blood_group VARCHAR(5) NOT NULL,
        quantity INT DEFAULT 0,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
        UNIQUE KEY unique_hospital_blood (hospital_id, blood_group)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Table 'hospital_inventory' created successfully.<br>";

    // 3. Create hospital_representatives table
    $sql = "CREATE TABLE IF NOT EXISTS hospital_representatives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        hospital_id INT NOT NULL,
        assigned_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_by) REFERENCES admins(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Table 'hospital_representatives' created successfully.<br>";

    // 4. Create blood_bookings table
    $sql = "CREATE TABLE IF NOT EXISTS blood_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        hospital_id INT NOT NULL,
        blood_group VARCHAR(5) NOT NULL,
        units INT NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'fulfilled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Table 'blood_bookings' created successfully.<br>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>