<?php
require_once 'config.php';
require_once 'chat_functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit();
}

$chatSystem = new ChatSystem($conn);
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$action = $data['action'] ?? ($_REQUEST['action'] ?? '');

try {
    switch ($action) {
        case 'send_message':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            // Get input data
            $input = file_get_contents('php://input');
            
            if (empty($input)) {
                throw new Exception('No input data received');
            }
            
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data: ' . json_last_error_msg());
            }
            
            $chatId = intval($data['chat_id'] ?? 0);
            $message = trim($data['message'] ?? '');
            $isEmergency = (bool)($data['is_emergency'] ?? false);
            
            if ($chatId <= 0) {
                throw new Exception('Invalid chat ID: ' . $chatId);
            }
            
            if (empty($message)) {
                throw new Exception('Message cannot be empty');
            }
            
            // Check if chat room exists
            $chatCheckQuery = "SELECT id, name FROM chat_rooms WHERE id = ?";
            $stmt = $conn->prepare($chatCheckQuery);
            if (!$stmt) {
                throw new Exception('Database prepare error for chat check: ' . $conn->error);
            }
            
            $stmt->bind_param("i", $chatId);
            $stmt->execute();
            $chatResult = $stmt->get_result()->fetch_assoc();
            
            if (!$chatResult) {
                throw new Exception('Chat room not found with ID: ' . $chatId);
            }
            
            // Verify user has access to this chat
            $accessQuery = "SELECT COUNT(*) as count FROM chat_participants WHERE chat_room_id = ? AND user_id = ?";
            $stmt = $conn->prepare($accessQuery);
            if (!$stmt) {
                throw new Exception('Database prepare error for access check: ' . $conn->error);
            }
            
            $stmt->bind_param("ii", $chatId, $_SESSION['user_id']);
            $stmt->execute();
            $accessResult = $stmt->get_result()->fetch_assoc();
            
            if ($accessResult['count'] == 0) {
                // Try to add user to chat if it's a global chat
                if ($chatResult['name'] === 'Global Emergency Chat') {
                    $addQuery = "INSERT IGNORE INTO chat_participants (chat_room_id, user_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($addQuery);
                    $stmt->bind_param("ii", $chatId, $_SESSION['user_id']);
                    $stmt->execute();
                } else {
                    throw new Exception('User does not have access to this chat');
                }
            }
            
            // Send the message
            $success = $chatSystem->sendMessage($chatId, $_SESSION['user_id'], $message, $isEmergency);
            
            if (!$success) {
                throw new Exception('Failed to insert message into database. MySQL Error: ' . $conn->error);
            }
            
            $messageId = $conn->insert_id;
            
            echo json_encode([
                'success' => true,
                'message_id' => $messageId
            ]);
            break;
            
        case 'get_new_messages':
            $chatId = intval($_GET['chat_id'] ?? 0);
            $lastMessageId = intval($_GET['last_message_id'] ?? 0);
            
            if ($chatId <= 0) {
                throw new Exception('Invalid chat ID');
            }
            
            $query = "SELECT m.*, u.fname, u.lname, u.picture 
                     FROM messages m
                     JOIN users u ON m.user_id = u.id
                     WHERE m.chat_room_id = ? AND m.id > ?
                     ORDER BY m.created_at ASC";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $conn->error);
            }
            
            $stmt->bind_param("ii", $chatId, $lastMessageId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>