<?php
// notification.php
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has admin access
checkAccess('admin');

// Fetch all notifications
$notifications = array();
$result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/notification.css">
</head>

<body>
    <div class="container mt-4">
        <!-- Notifications List -->
        <h3>All Notifications</h3>

        <div class="list-group">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <!-- Display only the message -->
                    <a href="#" class="list-group-item list-group-item-action">
                        <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning text-center">No notifications available.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/notification.js"></script>
</body>

</html>
