<?php
require_once 'config/db.php';

try {
    // Add 'education' column if it doesn't exist
    $pdo->exec("ALTER TABLE doctors ADD COLUMN education TEXT DEFAULT NULL");
    echo "Added 'education' column.\n";
} catch (PDOException $e) {
    echo "Column 'education' might already exist or error: " . $e->getMessage() . "\n";
}

try {
    // Add 'expertise' column if it doesn't exist
    $pdo->exec("ALTER TABLE doctors ADD COLUMN expertise TEXT DEFAULT NULL");
    echo "Added 'expertise' column.\n";
} catch (PDOException $e) {
    echo "Column 'expertise' might already exist or error: " . $e->getMessage() . "\n";
}

try {
    // Add 'available_hours' column if it doesn't exist
    $pdo->exec("ALTER TABLE doctors ADD COLUMN available_hours TEXT DEFAULT NULL");
    echo "Added 'available_hours' column.\n";
} catch (PDOException $e) {
    echo "Column 'available_hours' might already exist or error: " . $e->getMessage() . "\n";
}

echo "Database update complete.";
?>