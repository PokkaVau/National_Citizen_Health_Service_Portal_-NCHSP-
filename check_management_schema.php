<?php
require('config/db.php');
try {
    $tables = ['reports', 'medications', 'prescriptions', 'users', 'doctors', 'assistants', 'admins'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            echo "Table: $table\n";
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo "  " . $col['Field'] . " (" . $col['Type'] . ")\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "Table $table not found.\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>