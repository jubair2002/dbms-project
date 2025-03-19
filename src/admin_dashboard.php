<?php
// admin_dashboard.php
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has admin access
checkAccess('admin');

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

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="assets/css/adminDashboard.css">

    <title>CrisisLink Admin</title>
</head>

<body>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-happy'></i>
            <span class="text">CrisisLink Network</span>
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="dashboard.php">
                    <i class='bx bxs-home'></i>
                    <span class="text">Dashboard Summary</span>
                </a>
            </li>

            <li>
                <a href="user_management.php">
                    <i class='bx bxs-user-detail'></i>
                    <span class="text">Users</span>
                </a>
            </li>
            <li>
                <a href="message.php">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Message</span>
                </a>
            </li>
            <li>
                <a href="campaign.php">
                    <i class='bx bxs-help-circle'></i>
                    <span class="text">Campaign</span>
                </a>
            </li>
            <li>
                <a href="report.php">
                    <i class='bx bxs-file'></i>
                    <span class="text">Report</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="settings.php">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Settings</span>
                </a>
            </li>
            <li>
                <a href="profile.php">
                    <i class='bx bxs-user'></i>
                    <span class="text">Profile</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="logout">
                    <i class='bx bxs-exit'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->


    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
            <a href="#" class="notification">
                <i class='bx bxs-bell'></i>
                <span class="num"><?php echo $unread_count; ?></span>
            </a>
            <a class="profile">
                <span><?php echo htmlspecialchars($user['fname']); ?>(admin)</span> <!-- Displaying user's name -->
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>


            <ul class="box-info">
                <li>
                    <i class='bx bxs-calendar-check'></i>
                    <span class="text">
                        <h3>1020</h3>
                        <p>total user</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3>2834</h3>
                        <p>campaign</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-dollar-circle'></i>
                    <span class="text">
                        <h3>$2543</h3>
                        <p>Transections</p>
                    </span>
                </li>
            </ul>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="assets/js/adminDashboard.js"></script>
</body>

</html>