<?php
// notification_content.php - Without status badges
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has access
checkAccess('volunteer');

// Fetch all notifications
$notifications = array();
$result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5>All Notifications</h5>
    </div>
    <div class="card-body">
        <?php if (count($notifications) > 0): ?>
            <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item p-3 border-bottom">
                        <div class="notification-message">
                            <p class="mb-1"><strong><?php echo htmlspecialchars($notification['message']); ?></strong></p>
                            <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No notifications found.</p>
        <?php endif; ?>
    </div>
</div>