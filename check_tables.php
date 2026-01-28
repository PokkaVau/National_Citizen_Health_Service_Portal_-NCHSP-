<?php
require 'config/db.php';
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:<br>";
    echo "<pre>";
    print_r($tables);
    echo "</pre>";

    if (in_array('blood_requests', $tables)) {
        echo "<br>blood_requests table exists. Columns:<br>";
        $stmt = $pdo->query("DESCRIBE blood_requests");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "<br>blood_requests table does NOT exist.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>