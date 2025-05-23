<?php
// This file contains the implementation for the private chat functionality
// Save as user_search.php

require_once 'config.php';
require_once 'chat_functions.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$chatSystem = new ChatSystem($conn);
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$users = [];

// Search users if a search term is provided
if (!empty($searchTerm)) {
    $query = "SELECT id, fname, lname, user_type, status, picture 
              FROM users 
              WHERE (fname LIKE ? OR lname LIKE ? OR CONCAT(fname, ' ', lname) LIKE ?) 
              AND id != ? 
              ORDER BY fname, lname 
              LIMIT 20";
    
    $searchParam = "%" . $searchTerm . "%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $searchParam, $searchParam, $searchParam, $_SESSION['user_id']);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Process chat creation if requested
if (isset($_GET['create_chat']) && isset($_GET['user_id'])) {
    $otherUserId = (int)$_GET['user_id'];
    
    try {
        // Create or get private chat
        $chatId = $chatSystem->getOrCreatePrivateChat($_SESSION['user_id'], $otherUserId);
        
        // Redirect to the chat
        header("Location: message.php?chat_id=" . $chatId);
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Search - CrisisLink Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #ff0000;
            --light-color: #ffffff;
            --gray-color: #cccccc;
            --dark-gray: #666666;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--primary-color);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .header {
            background: var(--primary-color);
            color: var(--light-color);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
        }

        .back-btn {
            background: none;
            border: none;
            color: var(--light-color);
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-container {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid var(--gray-color);
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid var(--gray-color);
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: var(--transition);
        }

        .search-input:focus {
            border-color: var(--accent-color);
        }

        .search-btn {
            padding: 10px 20px;
            background: var(--accent-color);
            color: var(--light-color);
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: bold;
        }

        .search-btn:hover {
            opacity: 0.9;
        }

        .users-container {
            padding: 20px;
        }

        .user-list {
            list-style: none;
        }

        .user-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid var(--gray-color);
            transition: var(--transition);
        }

        .user-item:hover {
            background: #f8f9fa;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details h3 {
            margin: 0 0 5px;
            font-size: 16px;
        }

        .user-details p {
            margin: 0;
            color: var(--dark-gray);
            font-size: 14px;
        }

        .user-status {
            display: flex;
            align-items: center;
            font-size: 12px;
            color: var(--dark-gray);
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-online {
            background: var(--success-color);
        }

        .status-offline {
            background: var(--gray-color);
        }

        .chat-btn {
            padding: 8px 15px;
            background: var(--info-color);
            color: var(--light-color);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
        }

        .chat-btn:hover {
            opacity: 0.9;
        }

        .no-results {
            text-align: center;
            padding: 30px;
            color: var(--dark-gray);
            font-style: italic;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-search"></i> Find Users</h1>
            <a href="message.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Chats
            </a>
        </div>
        
        <div class="search-container">
            <form action="" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by name..." 
                    class="search-input"
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                    required
                >
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
        
        <div class="users-container">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($searchTerm) || empty($searchTerm)): ?>
                <div class="no-results">
                    <i class="fas fa-users fa-2x"></i>
                    <p>Search for users to start a private chat</p>
                </div>
            <?php elseif (empty($users)): ?>
                <div class="no-results">
                    <i class="fas fa-search fa-2x"></i>
                    <p>No users found matching "<?php echo htmlspecialchars($searchTerm); ?>"</p>
                </div>
            <?php else: ?>
                <ul class="user-list">
                    <?php foreach ($users as $user): ?>
                        <li class="user-item">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <img src="<?php echo $user['picture']; ?>" alt="User Avatar">
                                </div>
                                <div class="user-details">
                                    <h3><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></h3>
                                    <p><?php echo ucfirst($user['user_type']); ?></p>
                                    <div class="user-status">
                                        <span class="status-indicator <?php echo $user['status'] == 'active' ? 'status-online' : 'status-offline'; ?>"></span>
                                        <?php echo $user['status'] == 'active' ? 'Online' : 'Offline'; ?>
                                    </div>
                                </div>
                            </div>
                            <a href="?create_chat=1&user_id=<?php echo $user['id']; ?>&search=<?php echo isset($searchTerm) ? urlencode($searchTerm) : ''; ?>" class="chat-btn">
                                <i class="fas fa-comments"></i> Chat
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>