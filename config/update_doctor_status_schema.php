<?php
require 'db.php';

try {
    $pdo->beginTransaction();

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM doctors LIKE 'status'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add status column with default 'pending'
        // Existing doctors will be set to 'approved' to avoid locking them out
        $pdo->exec("ALTER TABLE doctors ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");

        // Update existing doctors to approved
        $pdo->exec("UPDATE doctors SET status = 'approved'");

        echo "✅ Column 'status' added successfully and existing doctors approved.<br>";
    } else {
        echo "ℹ️ Column 'status' already exists.<br>";
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage();
}
?>