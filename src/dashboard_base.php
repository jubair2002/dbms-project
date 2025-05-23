<?php
// dashboard_base.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Function to get user details from database
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT fname, lname, email, location, user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to check if user has access to current dashboard
function checkAccess($required_type) {
    if ($_SESSION['user_type'] == 'admin') {
        if ($required_type != 'admin') {
            header("Location: admin_dashboard.php");
            exit();
        }
    } else if ($_SESSION['user_type'] == 'volunteer') {
        if ($required_type != 'volunteer') {
            header("Location: volunteer_dashboard.php");
            exit();
        }
    } else {
        // Regular user
        if ($required_type != 'regular') {
            header("Location: user_dashboard.php");
            exit();
        }
    }
}
?>