<?php
require __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$recipient_id = null;
$recipient_type = null;

if (isset($_SESSION['user_id'])) {
    $recipient_id = $_SESSION['user_id'];
    $recipient_type = 'user';
} elseif (isset($_SESSION['admin_id'])) {
    $recipient_id = $_SESSION['admin_id'];
    $recipient_type = 'admin';
}

try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_id = ? AND recipient_type = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$recipient_id, $recipient_type]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'notifications' => $notifications]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>