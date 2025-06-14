<?php
require_once 'config.php';
require_once 'dashboard_base.php';

// Check if user has admin access
checkAccess('admin');

// Check if this is an AJAX request for user data
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_users') {
    // Get search parameters from AJAX request
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    $userTypeFilter = isset($_GET['user_type']) ? $_GET['user_type'] : '';
    $locationFilter = isset($_GET['location']) ? $_GET['location'] : '';

    $query = "SELECT id, fname, lname, email, phone, location, user_type, status, picture FROM users WHERE 1=1";
    $params = [];
    $types = "";

    // Add search condition if there's a search term
    if (!empty($searchTerm)) {
        $searchTerm = "%" . $searchTerm . "%"; // For LIKE query
        $query .= " AND (fname LIKE ? OR lname LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ssss";
    }

    // Add user type filter if selected
    if (!empty($userTypeFilter)) {
        $query .= " AND user_type = ?";
        $params[] = $userTypeFilter;
        $types .= "s";
    }

    // Add location filter if selected
    if (!empty($locationFilter)) {
        $query .= " AND location LIKE ?";
        $params[] = "%".$locationFilter."%";
        $types .= "s";
    }

    // Prepare and execute the query
    $stmt = $conn->prepare($query);

    // Bind parameters if we have any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Store users in an array
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    // Return users as JSON
    header('Content-Type: application/json');
    echo json_encode($users);
    exit;
}

// Check if this is an AJAX request for updating user status
if (isset($_GET['ajax']) && $_GET['ajax'] == 'update_status') {
    // Initialize response
    $response = ['success' => false];

    // Handle user status update (active/inactive)
    if (isset($_GET['action']) && isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $action = $_GET['action'];

        if ($action == 'deactivate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $success = $stmt->execute();
            $response['success'] = $success;
        } elseif ($action == 'activate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $success = $stmt->execute();
            $response['success'] = $success;
        }
    }

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// For the initial page load, we'll still fetch users to have data before any search
$users = [];
$stmt = $conn->prepare("SELECT id, fname, lname, email, phone, location, user_type, status, picture FROM users LIMIT 50"); // Limit to 50 users for initial load
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Helper function to get user initials
function getUserInitials($fname, $lname) {
    $firstInitial = !empty($fname) ? strtoupper($fname[0]) : '';
    $lastInitial = !empty($lname) ? strtoupper($lname[0]) : '';
    return $firstInitial . $lastInitial ?: 'U'; // Default to 'U' if no name
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="assets/css/userManagement.css"> <!-- Your external CSS file -->
</head>

<body>
    <div class="container">
        <h2>User Management</h2>

        <!-- Search and Filter Form -->
        <div class="search-container">
            <form id="searchForm">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" id="searchInput" placeholder="Search by name, email, phone">
                    </div>
                    <div class="col-md-3">
                        <select name="user_type" class="form-control" id="userTypeSelect">
                            <option value="">Select User Type</option>
                            <option value="volunteer">Volunteer</option>
                            <option value="regular">Regular</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="location" class="form-control" id="locationInput" placeholder="Filter by location">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Loading indicator -->
        <div id="loadingIndicator" class="text-center my-3" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- No results message -->
        <div id="noResultsMessage" class="alert alert-warning" style="display: none;">No results found</div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>User Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($user['picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Profile" class="user-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="user-avatar-placeholder" style="display:none;"><?php echo getUserInitials($user['fname'], $user['lname']); ?></div>
                                    <?php else: ?>
                                        <div class="user-avatar-placeholder"><?php echo getUserInitials($user['fname'], $user['lname']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['fname']); ?></td>
                                <td><?php echo htmlspecialchars($user['lname']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['location']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn <?php echo $user['status'] == 'active' ? 'btn-danger' : 'btn-success'; ?> status-btn" 
                                            data-user-id="<?php echo $user['id']; ?>" 
                                            data-action="<?php echo $user['status'] == 'active' ? 'deactivate' : 'activate'; ?>">
                                        <?php echo $user['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="assets/js/userManagement.js"></script>
</body>

</html>