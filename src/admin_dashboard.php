<?php
// First, modify the users table to add admin type:
// ALTER TABLE users MODIFY COLUMN user_type ENUM('admin', 'volunteer', 'regular') NOT NULL;

// admin_dashboard.php
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has admin access
checkAccess('admin');

// Get user details
$user = getUserDetails($conn, $_SESSION['user_id']);

// Get statistics
$stats = array(
    'total_users' => 0,
    'total_volunteers' => 0,
    'total_regular' => 0,
    'new_users_today' => 0
);

// Get total users count
$result = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN user_type = 'volunteer' THEN 1 ELSE 0 END) as volunteers,
    SUM(CASE WHEN user_type = 'regular' THEN 1 ELSE 0 END) as regular_users,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_today
    FROM users WHERE user_type != 'admin'");

if ($result) {
    $stats = $result->fetch_assoc();
}

// Get recent users
$recent_users = array();
$result = $conn->query("SELECT id, fname, lname, email, user_type, created_at 
    FROM users WHERE user_type != 'admin' 
    ORDER BY created_at DESC LIMIT 5");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CrisisLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">CrisisLink Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">View Site</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($user['fname']); ?> (Admin)
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="admin_dashboard.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                            <a href="admin_users.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2"></i> Manage Users
                            </a>
                            <a href="admin_volunteers.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-hands-helping me-2"></i> Volunteers
                            </a>
                            <a href="admin_requests.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-tasks me-2"></i> Help Requests
                            </a>
                            <a href="admin_reports.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-bar me-2"></i> Reports
                            </a>
                            <a href="admin_settings.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Total Users</h6>
                                        <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-users fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Volunteers</h6>
                                        <h2 class="mb-0"><?php echo $stats['volunteers']; ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-hands-helping fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Regular Users</h6>
                                        <h2 class="mb-0"><?php echo $stats['regular_users']; ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-user fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">New Today</h6>
                                        <h2 class="mb-0"><?php echo $stats['new_today']; ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-user-plus fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['user_type'] == 'volunteer' ? 'success' : 'primary'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($user['user_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <a href="admin_user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-plus me-2"></i> Add New User
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-download me-2"></i> Download User Report
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-envelope me-2"></i> Send Mass Email
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <i class="fas fa-cog me-2"></i> System Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">System Status</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        System Status
                                        <span class="badge bg-success">Online</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Database Status
                                        <span class="badge bg-success">Connected</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Last Backup
                                        <span class="badge bg-info">Today 03:00 AM</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Server Load
                                        <span class="badge bg-success">Normal</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            // Add your delete logic here
            // You might want to make an AJAX call to a PHP endpoint
        }
    }
    </script>
</body>
</html>