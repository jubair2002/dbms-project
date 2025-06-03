<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE recipient_id = ? AND is_read = FALSE");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

echo json_encode([
    'unread_count' => (int)$row['unread_count']
]);

$conn->close();
?>