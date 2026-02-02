<?php
// Function to create a notification
function createNotification($pdo, $recipient_id, $recipient_type, $message, $type = 'info', $link = null)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, recipient_type, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$recipient_id, $recipient_type, $message, $type, $link]);
        return true;
    } catch (PDOException $e) {
        // Silently fail or log error to avoid interrupting main flow
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}
?>