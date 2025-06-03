<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$stmt = $conn->prepare("
    SELECT n.*, u.fname, u.lname 
    FROM notifications n
    LEFT JOIN users u ON n.sender_id = u.id
    WHERE n.recipient_id = ?
    ORDER BY n.created_at DESC
    LIMIT 15
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (count($notifications) > 0) {
    foreach ($notifications as $notification) {
        echo '<div class="notification-item ' . ($notification['is_read'] ? '' : 'unread') . '">';
        echo '<div class="notification-title">' . htmlspecialchars($notification['title']) . '</div>';
        echo '<div class="notification-message">' . htmlspecialchars($notification['message']) . '</div>';
        echo '<div class="notification-time">';
        echo date('M j, Y g:i a', strtotime($notification['created_at']));
        if (!$notification['is_read']) {
echo ' <a href="#" onclick="markAsRead(' . $notification['id'] . ', this); return false;" class="mark-read">Mark as read</a>';        }
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div style="padding: 20px; text-align: center; color: #777;">No notifications found</div>';
}

$conn->close();
?>