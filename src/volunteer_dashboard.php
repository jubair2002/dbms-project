<?php
// admin_dashboard.php
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has volunteer access
checkAccess('volunteer');

// Get user details
$user = getUserDetails($conn, $_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="assets/css/volunteerDashboard.css">

    <style>
        /* Iframe Specific Styles */
        #content-iframe {
            width: 100%;
            border: none;
            min-height: calc(100vh - 100px); /* Adjust based on your navbar height */
        }
        .side-menu a {
            cursor: pointer;
        }
    </style>

    <title>CrisisLink Volunteer</title>
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
                <a onclick="loadContent('dashboardSummary.php')">
                    <i class='bx bxs-home'></i>
                    <span class="text">Dashboard Summary</span>
                </a>
            </li>
            <li>
                <a onclick="loadContent('assignments.php')">
                    <i class='bx bxs-stopwatch'></i>
                    <span class="text">Assignment</span>
                </a>
            </li>
            <li>
                <a onclick="loadContent('message.php')">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Message</span>
                </a>
            </li>
            <li>
                <a onclick="loadContent('campaign.php')">
                    <i class='bx bxs-megaphone'></i>
                    <span class="text">Campaign</span>
                </a>
            </li>
            <li>
                <a onclick="loadContent('report.php')">
                    <i class='bx bxs-file'></i>
                    <span class="text">Report</span>
                </a>
            </li>
            <li>
                <a onclick="loadContent('emergency.php')">
                    <i class='bx bxs-first-aid'></i>
                    <span class="text">Emergency Help</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a onclick="loadContent('settings.php')">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Settings</span>
                </a>
            </li>
            <li>
                <a onclick="loadContent('profile.php')">
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
            </a>
            <a class="profile">
                <span><?php echo htmlspecialchars($user['fname']); ?>(volunteer)</span>
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN - Replace with iframe -->
        <main>
            <iframe id="content-iframe" src="dashboardSummary.php" frameborder="0"></iframe>
        </main>
    </section>
    <!-- CONTENT -->

    <script>
    function loadContent(url) {
        // Update active state in sidebar
        document.querySelectorAll('.side-menu li, .side-menu.top li').forEach(li => {
            li.classList.remove('active');
        });
        event.target.closest('li').classList.add('active');

        // Load content in iframe
        document.getElementById('content-iframe').src = url;
    }

    // Optional: Resize iframe to content
    document.getElementById('content-iframe').addEventListener('load', function() {
        try {
            this.style.height = this.contentWindow.document.body.scrollHeight + 'px';
        } catch(e) {
            console.log('Could not resize iframe');
        }
    });
    </script>

    <script src="assets/js/volunteerDashboard.js"></script>
</body>
</html>