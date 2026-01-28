<?php
require('config/db.php');
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in users table: " . implode(", ", $columns) . "\n";

    $stmt = $pdo->query("SHOW CREATE TABLE users");
    $row = $stmt->fetch();
    echo "\nCreate Table Statement:\n" . $row[1] . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>