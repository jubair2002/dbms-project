<?php
// admin_dashboard.php
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has admin access
checkAccess('volunteer');

// Get user details
$user = getUserDetails($conn, $_SESSION['user_id']);

// Fetch notifications from the database
$notifications = array();
$result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5"); // Get the latest 5 notifications

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Count the total unread notifications
$unread_count_result = $conn->query("SELECT COUNT(*) AS unread_count FROM notifications WHERE status = 'unread'");
$unread_count = $unread_count_result->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CrisisLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/volunteerDashboard.css"> <!-- Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Section -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h4>CrisisLink Network</h4>
            </div>
            <div class="sidebar-content">
                <ul class="sidebar-list">
                    <li><a href="javascript:void(0)" onclick="return loadPage('dashboard.php')">Dashboard Summary</a></li>
                    <li><a href="javascript:void(0)" onclick="return loadPage('assignment.php')">Assignment</a></li>
                    <li><a href="javascript:void(0)" onclick="return loadPage('report.php')">Reports</a></li>
                    <li><a href="javascript:void(0)" onclick="return loadPage('campaign.php')">Campaign</a></li>
                    <li><a href="javascript:void(0)" onclick="return loadPage('chat.php')">Chat</a></li>
                    <li><a href="javascript:void(0)" onclick="return loadPage('donations.php')">Donations</a></li>
                    <li><a href="javascript:void(0)" onclick="return loadPage('settings.php')">Settings</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content Container -->
        <div class="main-content" id="mainContent">
            <!-- Navigation (Top Navbar) -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="volunteer_dashboard.php">Home</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <!-- Notifications Button -->
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="#" id="notificationsBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-bell"></i>
                                    <span class="badge bg-danger" id="notification-count"><?php echo $unread_count; ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsBtn" id="notificationList">
                                    <?php if (count($notifications) > 0): ?>
                                        <?php foreach ($notifications as $notification): ?>
                                            <li class="dropdown-item">
                                                <p><strong><?php echo htmlspecialchars($notification['message']); ?></strong></p>
                                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="dropdown-item">No new notifications.</li>
                                    <?php endif; ?>
                                    <li class="dropdown-item see-all">
                                        <a href="javascript:void(0)" onclick="return loadPage('notification_content.php')">See All</a>
                                    </li>
                                </ul>
                            </li>

                            <!-- Profile Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo htmlspecialchars($user['fname']); ?> (volunteer)
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="return loadPage('profile.php')">Profile</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="container-fluid mt-4 content-area">
                <div id="contentArea" class="bg-white p-3 rounded shadow-sm">
                    <!-- Content will be loaded here -->
                    <div class="text-center p-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/volunteerDashboard.js"></script> <!-- Custom JS -->
</body>

</html>