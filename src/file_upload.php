<?php
// file_upload.php - Save this as a new file
require_once 'config.php';
require_once 'chat_functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error logging
error_log("File upload started");
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit();
}

// Function to create directories if they don't exist
function createDirectoryIfNotExists($dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Function to check file size
function checkFileSize($file, $maxSize = 5242880) { // 5MB in bytes
    return $file['size'] <= $maxSize;
}

// Function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to check if file type is allowed
function isAllowedFileType($extension) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'csv', 'xlsx', 'pptx', 'mp4', 'mp3'];
    return in_array($extension, $allowedTypes);
}

// Function to generate a unique filename
function generateUniqueFilename($filename) {
    $extension = getFileExtension($filename);
    $basename = basename($filename, '.' . $extension);
    return $basename . '_' . time() . '_' . substr(md5(rand()), 0, 10) . '.' . $extension;
}

// Check if a file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = isset($_FILES['file']) ? 'Upload error: ' . $_FILES['file']['error'] : 'No file uploaded';
    error_log("File upload error: " . $errorMessage);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $errorMessage
    ]);
    exit();
}

$uploadedFile = $_FILES['file'];
$chatId = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

error_log("Chat ID: " . $chatId);
error_log("Message: " . $message);

// Validate chat ID
if ($chatId <= 0) {
    error_log("Invalid chat ID: " . $chatId);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid chat ID'
    ]);
    exit();
}

// Get file information
$fileName = $uploadedFile['name'];
$fileSize = $uploadedFile['size'];
$fileTmpPath = $uploadedFile['tmp_name'];
$fileExtension = getFileExtension($fileName);

error_log("File name: " . $fileName);
error_log("File size: " . $fileSize);
error_log("File extension: " . $fileExtension);

// Check file size
if (!checkFileSize($uploadedFile)) {
    error_log("File size exceeds limit");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'File size exceeds the limit of 5MB'
    ]);
    exit();
}

// Check file type
if (!isAllowedFileType($fileExtension)) {
    error_log("File type not allowed: " . $fileExtension);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'File type not allowed'
    ]);
    exit();
}

// Create directories if they don't exist
$uploadDir = 'uploads/';
$chatDir = $uploadDir . 'chat_' . $chatId . '/';
createDirectoryIfNotExists($uploadDir);
createDirectoryIfNotExists($chatDir);

error_log("Upload directory: " . $uploadDir);
error_log("Chat directory: " . $chatDir);

// Generate a unique filename
$newFileName = generateUniqueFilename($fileName);
$filePath = $chatDir . $newFileName;

error_log("New file name: " . $newFileName);
error_log("File path: " . $filePath);

// Move the uploaded file
if (move_uploaded_file($fileTmpPath, $filePath)) {
    error_log("File moved successfully");
    // File was successfully uploaded, now save to database
    $fileUrl = $filePath;
    
    // Create default message if none provided
    if (empty($message)) {
        $message = 'Shared a file: ' . $fileName;
    }
    
    try {
        $chatSystem = new ChatSystem($conn);
        $success = $chatSystem->sendMessage($chatId, $_SESSION['user_id'], $message, false, $fileUrl);
        
        if ($success) {
            $messageId = $conn->insert_id;
            error_log("Message saved to database. ID: " . $messageId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message_id' => $messageId,
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'file_type' => $fileExtension
            ]);
        } else {
            error_log("Failed to save message to database");
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save message to database'
            ]);
        }
    } catch (Exception $e) {
        error_log("Exception: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    error_log("Failed to move uploaded file");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Failed to move uploaded file'
    ]);
}
?>