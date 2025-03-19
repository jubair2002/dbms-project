<?php
// dashboard_summary.php
require_once 'config.php';
require_once 'dashboard_base.php';

// Get the total number of users and volunteers (same as before)
$total_users_result = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE status = 'active'");
$total_users = $total_users_result->fetch_assoc()['total_users'];

$total_volunteers_result = $conn->query("SELECT COUNT(*) AS total_volunteers FROM users WHERE status = 'active' AND user_type = 'volunteer'");
$total_volunteers = $total_volunteers_result->fetch_assoc()['total_volunteers'];
?>

<div class="dashboard-summary">
    <h2>Dashboard Summary</h2>

    <ul class="box-info">
        <li>
            <i class='bx bxs-calendar-check'></i>
            <span class="text">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </span>
        </li>
        <li>
            <i class='bx bxs-group'></i>
            <span class="text">
                <h3><?php echo $total_volunteers; ?></h3>
                <p>Total Volunteers</p>
            </span>
        </li>
        <li>
            <i class='bx bxs-dollar-circle'></i>
            <span class="text">
                <h3>$2543</h3>
                <p>Transactions</p>
            </span>
        </li>
    </ul>
</div>
