<?php
require('config/db.php');

try {
    $sql = "CREATE TABLE IF NOT EXISTS donor_access_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        request_id INT NOT NULL,
        accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'donor_access_logs' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>