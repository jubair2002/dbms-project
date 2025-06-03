<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(400);
    die("Unauthorized");
}

$notification_id = intval($_GET['id']);
$stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND recipient_id = ?");
$stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
$success = $stmt->execute();
$stmt->close();

// Always return JSON response for AJAX
header('Content-Type: application/json');
echo json_encode(['success' => $success]);
exit();
?>