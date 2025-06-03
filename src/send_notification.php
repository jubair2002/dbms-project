<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$volunteer_id = intval($input['volunteer_id']);
$task_id = intval($input['task_id']);
$sender_id = $_SESSION['user_id'];

// Get task details
$task_sql = "SELECT task_name, deadline FROM tasks WHERE id = ?";
$task_stmt = $conn->prepare($task_sql);
$task_stmt->bind_param("i", $task_id);
$task_stmt->execute();
$task_result = $task_stmt->get_result()->fetch_assoc();

// Insert notification
$title = "Task Reminder";
$message = "You have a pending task: " . $task_result['task_name'] . " (Deadline: " . $task_result['deadline'] . ")";

$sql = "INSERT INTO notifications (recipient_id, sender_id, title, message, entity_type, entity_id) VALUES (?, ?, ?, ?, 'task', ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissi", $volunteer_id, $sender_id, $title, $message, $task_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send notification']);
}
?>