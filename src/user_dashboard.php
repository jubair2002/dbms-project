<?php
// user_dashboard.php
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has regular user access
checkAccess('regular');

// Get user details
$user = getUserDetails($conn, $_SESSION['user_id']);

// Get current page from URL parameter or use dashboard as default
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboardSummary';
$valid_pages = ['dashboardSummary', 'requests', 'messages', 'profile', 'settings'];

// Validate the page parameter
if (!in_array($current_page, $valid_pages)) {
    $current_page = 'dashboardSummary';
}

// Set the page file to load
$page_file = $current_page . '.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="assets/css/userDashboard.css">

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
        /* Override colors to match design */
        :root {
            --light: #F9F9F9;
            --green: #4CAF50;
            --light-green: #e8f5e9;
            --grey: #eee;
            --dark-grey: #AAAAAA;
            --dark: #342E37;
            --red: #DB504A;
        }
        
        /* Override blue with green */
        #sidebar .brand {
            color: var(--green);
        }
        #sidebar .side-menu.top li.active a {
            color: var(--green);
        }
        #sidebar .side-menu.top li a:hover {
            color: var(--green);
        }
        #content nav .nav-link:hover {
            color: var(--green);
        }
        #content main .head-title .left .breadcrumb li a.active {
            color: var(--green);
        }
        #content main .head-title .btn-download {
            background: var(--green);
        }
        #content main .box-info li:nth-child(1) .bx {
            background: var(--light-green);
            color: var(--green);
        }
        #content main .table-data .order table tr td .status.completed {
            background: var(--green);
        }
        #content main .table-data .todo .todo-list li.completed {
            border-left: 10px solid var(--green);
        }
    </style>

    <title>CrisisLink User</title>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-happy'></i>
            <span class="text">CrisisLink Network</span>
        </a>
        <ul class="side-menu top">
            <li <?php echo ($current_page == 'dashboardSummary') ? 'class="active"' : ''; ?>>
                <a href="?page=dashboardSummary">
                    <i class='bx bxs-home'></i>
                    <span class="text">Dashboard Summary</span>
                </a>
            </li>
            <li <?php echo ($current_page == 'requests') ? 'class="active"' : ''; ?>>
                <a href="?page=requests">
                    <i class='bx bxs-notepad'></i>
                    <span class="text">My Requests</span>
                </a>
            </li>
            <li <?php echo ($current_page == 'messages') ? 'class="active"' : ''; ?>>
                <a href="?page=messages">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Messages</span>
                </a>
            </li>
            <li <?php echo ($current_page == 'profile') ? 'class="active"' : ''; ?>>
                <a href="?page=profile">
                    <i class='bx bxs-user'></i>
                    <span class="text">Profile</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li <?php echo ($current_page == 'settings') ? 'class="active"' : ''; ?>>
                <a href="?page=settings">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Settings</span>
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
            <div style="flex-grow: 1;"></div>
            <a href="#" class="notification">
                <i class='bx bxs-bell'></i>
            </a>
            <a class="profile">
                <span><?php echo htmlspecialchars($user['fname']); ?> (user)</span>
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN - Replace with iframe -->
        <main>
            <iframe id="content-iframe" src="<?php echo htmlspecialchars($page_file); ?>" frameborder="0"></iframe>
        </main>
    </section>
    <!-- CONTENT -->

    <script>
    // TOGGLE SIDEBAR
    const menuBar = document.querySelector('#content nav .bx.bx-menu');
    const sidebar = document.getElementById('sidebar');

    menuBar.addEventListener('click', function () {
        sidebar.classList.toggle('hide');
    });

    // RESPONSIVE BEHAVIOR
    if(window.innerWidth < 768) {
        sidebar.classList.add('hide');
    }

    window.addEventListener('resize', function () {
        if(this.innerWidth < 768) {
            sidebar.classList.add('hide');
        }
    });

    // Optional: Resize iframe to content
    document.getElementById('content-iframe').addEventListener('load', function() {
        try {
            this.style.height = this.contentWindow.document.body.scrollHeight + 'px';
        } catch(e) {
            console.log('Could not resize iframe');
        }
    });
    </script>
</body>
</html>