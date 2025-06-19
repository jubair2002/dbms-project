<?php
require_once 'config.php';
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['task_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$task_id = intval($_POST['task_id']);
$new_status = $_POST['status'];
$user_id = $_SESSION['user_id'];

// Validate status
$allowed_statuses = ['assigned', 'in-progress', 'completed'];
if (!in_array($new_status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Verify that this task belongs to the current volunteer
$verify_sql = "SELECT t.id 
               FROM tasks t
               INNER JOIN assignments a ON t.assignment_id = a.id
               WHERE t.id = ? AND a.volunteer_id = ?";

$verify_stmt = $conn->prepare($verify_sql);
if (!$verify_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error']);
    exit();
}

$verify_stmt->bind_param("ii", $task_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Task not found or not assigned to you']);
    exit();
}

// Update the task status
$update_sql = "UPDATE tasks SET status = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);

if (!$update_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error']);
    exit();
}

$update_stmt->bind_param("si", $new_status, $task_id);

if ($update_stmt->execute()) {
    // If task is completed, also update assignment status
    if ($new_status === 'completed') {
        $assignment_sql = "UPDATE assignments a
                          INNER JOIN tasks t ON t.assignment_id = a.id
                          SET a.status = 'completed'
                          WHERE t.id = ?";
        
        $assignment_stmt = $conn->prepare($assignment_sql);
        if ($assignment_stmt) {
            $assignment_stmt->bind_param("i", $task_id);
            $assignment_stmt->execute();
            $assignment_stmt->close();
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Task status updated successfully',
        'new_status' => $new_status
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
}

// Close connections
$verify_stmt->close();
$update_stmt->close();
$conn->close();
?>