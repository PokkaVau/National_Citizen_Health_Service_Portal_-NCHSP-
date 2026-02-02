<?php
require __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['id'] ?? null;
$mark_all = $data['mark_all'] ?? false;

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
    if ($mark_all) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_id = ? AND recipient_type = ?");
        $stmt->execute([$recipient_id, $recipient_type]);
        echo json_encode(['success' => true, 'message' => 'All marked as read']);
    } elseif ($notification_id) {
        // Ensure notification belongs to user
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient_id = ? AND recipient_type = ?");
        $stmt->execute([$notification_id, $recipient_id, $recipient_type]);
        echo json_encode(['success' => true, 'message' => 'Marked as read']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid Request']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>