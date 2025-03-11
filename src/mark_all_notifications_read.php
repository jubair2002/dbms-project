<?php
// mark_all_notifications_read.php
require_once 'config.php';

// Update all notifications' status to 'read'
$query = "UPDATE notifications SET status = 'read' WHERE status = 'unread'";

if ($conn->query($query) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
