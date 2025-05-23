<?php
require_once 'config.php';
require_once 'chat_functions.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$chatSystem = new ChatSystem($conn);

// Get current user info
$userQuery = "SELECT id, fname, lname, user_type, status, picture FROM users WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

// Check if user exists
if (!$currentUser) {
    echo "User not found!";
    exit();
}

// Get user's chat rooms
$chatRooms = $chatSystem->getUserChatRooms($_SESSION['user_id']);

// Initialize global chat if it doesn't exist
$globalChat = null;
foreach ($chatRooms as $chat) {
    if ($chat['type'] == 'global') {
        $globalChat = $chat;
        break;
    }
}

if (!$globalChat) {
    // Create global chat if it doesn't exist
    $conn->query("INSERT IGNORE INTO chat_rooms (name, type, created_by) VALUES ('Global Emergency Chat', 'global', 1)");
    $globalChatId = $conn->insert_id;

    // If no ID was generated (record already existed), get the existing one
    if ($globalChatId == 0) {
        $result = $conn->query("SELECT id FROM chat_rooms WHERE type = 'global' AND name = 'Global Emergency Chat' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $globalChatId = $result->fetch_assoc()['id'];
        }
    }

    // Add all users to global chat
    $conn->query("INSERT IGNORE INTO chat_participants (chat_room_id, user_id) 
                  SELECT $globalChatId, id FROM users WHERE user_type IN ('admin', 'volunteer', 'regular')");

    // Refresh chat rooms after creating global chat
    $chatRooms = $chatSystem->getUserChatRooms($_SESSION['user_id']);

    // Get the newly created/found global chat
    foreach ($chatRooms as $chat) {
        if ($chat['type'] == 'global') {
            $globalChat = $chat;
            break;
        }
    }
}

// If still no global chat found, create a fallback
if (!$globalChat) {
    $globalChat = [
        'id' => 1,
        'name' => 'Global Emergency Chat',
        'type' => 'global'
    ];
}

// Get current chat (default to global)
$currentChatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : $globalChat['id'];
$currentChat = null;

foreach ($chatRooms as $chat) {
    if ($chat['id'] == $currentChatId) {
        $currentChat = $chat;
        break;
    }
}

// If current chat not found, default to global
if (!$currentChat) {
    $currentChatId = $globalChat['id'];
    $currentChat = $globalChat;
}

// Get messages for current chat
$messages = $chatSystem->getChatMessages($currentChatId, $_SESSION['user_id']);

// Get participants for current chat
$participants = $chatSystem->getChatParticipants($currentChatId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0">
    <title>CrisisLink Chat System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/message.css">
    <style>
        /* Additional inline styles to ensure full screen */
        html, body {
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
        
        .chat-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            max-width: none;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1><i class="fas fa-comments"></i> CrisisLink Chat</h1>
                <div class="user-info">
                    <div class="user-avatar" id="currentUserAvatar">
                        <img src="<?php echo $currentUser['picture']; ?>" alt="User Avatar">
                    </div>
                    <div class="user-status">
                        <div class="user-name" id="currentUserName"><?php echo htmlspecialchars($currentUser['fname'] . ' ' . $currentUser['lname']); ?></div>
                        <div class="user-role" id="currentUserRole">
                            <?php echo ucfirst($currentUser['user_type']); ?>
                            <span class="online-indicator"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Tabs -->
            <div class="chat-tabs">
                <button class="tab-btn <?php echo ($currentChat['type'] == 'global') ? 'active' : ''; ?>" data-tab="global" onclick="switchTab('global')">
                    <i class="fas fa-globe"></i> Global
                </button>
                <button class="tab-btn <?php echo ($currentChat['type'] == 'campaign') ? 'active' : ''; ?>" data-tab="campaigns" onclick="switchTab('campaigns')">
                    <i class="fas fa-users"></i> Groups
                </button>
                <button class="tab-btn <?php echo ($currentChat['type'] == 'private') ? 'active' : ''; ?>" data-tab="private" onclick="switchTab('private')">
                    <i class="fas fa-user"></i> Private
                </button>
            </div>

            <!-- Chat Lists -->
            <div class="chat-lists">
                <!-- Global Chat List -->
                <div class="chat-list <?php echo ($currentChat['type'] == 'global') ? 'active' : ''; ?>" id="global-list">
                    <div class="chat-item <?php echo ($currentChat['type'] == 'global') ? 'active' : ''; ?>"
                        data-chat-id="<?php echo $globalChat['id']; ?>"
                        data-chat-type="global"
                        onclick="selectChat(<?php echo $globalChat['id']; ?>, 'global')">
                        <div class="chat-item-header">
                            <div class="chat-name">
                                <i class="fas fa-globe"></i> Global Community Chat
                                <span class="emergency-badge">LIVE</span>
                            </div>
                            <div class="chat-time">Now</div>
                        </div>
                        <div class="chat-preview">Emergency coordination channel for all users</div>
                    </div>
                </div>

                <!-- Campaign Groups List -->
                <div class="chat-list <?php echo ($currentChat['type'] == 'campaign') ? 'active' : ''; ?>" id="campaigns-list">
                    <?php foreach ($chatRooms as $chat): ?>
                        <?php if ($chat['type'] == 'campaign'): ?>
                            <div class="chat-item <?php echo ($chat['id'] == $currentChatId) ? 'active' : ''; ?>"
                                data-chat-id="<?php echo $chat['id']; ?>"
                                data-chat-type="campaign"
                                onclick="selectChat(<?php echo $chat['id']; ?>, 'campaign')">
                                <div class="chat-item-header">
                                    <div class="chat-name">
                                        <i class="fas fa-users"></i> <?php echo htmlspecialchars($chat['name']); ?>
                                    </div>
                                    <div class="chat-time">Now</div>
                                </div>
                                <div class="chat-preview">Campaign group chat</div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Private Chats List -->
                <div class="chat-list <?php echo ($currentChat['type'] == 'private') ? 'active' : ''; ?>" id="private-list">
                    <?php foreach ($chatRooms as $chat): ?>
                        <?php if ($chat['type'] == 'private'): ?>
                            <div class="chat-item <?php echo ($chat['id'] == $currentChatId) ? 'active' : ''; ?>"
                                data-chat-id="<?php echo $chat['id']; ?>"
                                data-chat-type="private"
                                onclick="selectChat(<?php echo $chat['id']; ?>, 'private')">
                                <div class="chat-item-header">
                                    <div class="chat-name">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($chat['name']); ?>
                                        <span style="color: #28a745; font-size: 10px;">● Online</span>
                                    </div>
                                    <div class="chat-time">Now</div>
                                </div>
                                <div class="chat-preview">Private conversation</div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="chat-item new-chat-button" onclick="window.location.href='user_search.php'">
                        <div class="chat-item-header">
                            <div class="chat-name">
                                <i class="fas fa-plus-circle"></i> Start New Private Chat
                            </div>
                        </div>
                        <div class="chat-preview">Search for users to chat with</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="main-chat">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-header-avatar" id="chatAvatar"><?php echo strtoupper(substr($currentChat['name'], 0, 1)); ?></div>
                    <div class="chat-header-details">
                        <h3 id="chatTitle"><?php echo htmlspecialchars($currentChat['name']); ?></h3>
                        <p id="chatSubtitle"><?php echo count($participants); ?> participants • <?php echo ucfirst($currentChat['type']); ?> chat</p>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="action-btn btn-info" onclick="showChatInfo()">
                        <i class="fas fa-info-circle"></i> Info
                    </button>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="messages-container" id="messagesContainer">
                <!-- System Message -->
                <?php if ($currentChat['type'] == 'global'): ?>
                    <div class="system-message">
                        <i class="fas fa-info-circle"></i> Welcome to Global Emergency Chat. Stay coordinated and informed.
                    </div>
                <?php endif; ?>

                <!-- Messages from database -->
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo ($message['user_id'] == $_SESSION['user_id']) ? 'own' : ''; ?> <?php echo $message['is_emergency'] ? 'emergency-message' : ''; ?>">
                        <div class="message-avatar">
                            <img src="<?= $message['picture']; ?>" alt="User Avatar">
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender"><?php echo htmlspecialchars($message['fname'] . ' (' . $message['user_type'] . ')'); ?></span>
                                <span class="message-time"><?php echo date('g:i A', strtotime($message['created_at'])); ?></span>
                            </div>
                            <div class="message-text"><?php echo htmlspecialchars($message['message']); ?></div>

                            <?php if (!empty($message['attachment_url'])): ?>
                                <div class="message-attachment">
                                    <?php
                                    $ext = strtolower(pathinfo($message['attachment_url'], PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                    $isPdf = $ext === 'pdf';
                                    $isVideo = in_array($ext, ['mp4', 'webm']);
                                    $isAudio = in_array($ext, ['mp3', 'wav', 'ogg']);

                                    if ($isImage): ?>
                                        <div class="attachment-preview">
                                            <img src="<?php echo $message['attachment_url']; ?>" alt="Attached image" class="attachment-image">
                                        </div>
                                    <?php elseif ($isPdf): ?>
                                        <div class="attachment-file pdf-file">
                                            <i class="fas fa-file-pdf"></i>
                                            <a href="<?php echo $message['attachment_url']; ?>" target="_blank" class="attachment-link">
                                                View PDF Document
                                            </a>
                                        </div>
                                    <?php elseif ($isVideo): ?>
                                        <div class="attachment-preview">
                                            <video controls class="attachment-video">
                                                <source src="<?php echo $message['attachment_url']; ?>" type="video/<?php echo $ext; ?>">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    <?php elseif ($isAudio): ?>
                                        <div class="attachment-preview">
                                            <audio controls class="attachment-audio">
                                                <source src="<?php echo $message['attachment_url']; ?>" type="audio/<?php echo $ext; ?>">
                                                Your browser does not support the audio tag.
                                            </audio>
                                        </div>
                                    <?php else: ?>
                                        <div class="attachment-file">
                                            <i class="fas fa-file"></i>
                                            <a href="<?php echo $message['attachment_url']; ?>" download class="attachment-link">
                                                Download Attachment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Typing Indicator -->
                <div class="typing-indicator" id="typingIndicator" style="display: none;">
                    <strong>Someone</strong> is typing
                    <span class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </div>
            </div>

            <!-- Message Input -->
            <div class="message-input-container">
                <div class="message-input-wrapper">
                    <textarea
                        class="message-input"
                        id="messageInput"
                        placeholder="Type your message... (Press Enter to send, Shift+Enter for new line)"
                        rows="1"></textarea>
                    <div class="input-actions">
                        <button class="input-btn btn-attachment" onclick="attachFile()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button class="input-btn btn-send" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                <input type="file" class="file-upload" id="fileUpload" accept="image/*,video/*,.pdf,.doc,.docx">
            </div>
        </div>
    </div>

    <!-- Pass PHP variables to JavaScript -->
    <script>
        // Global variables from PHP
        const currentChat = {
            id: <?php echo $currentChatId; ?>,
            type: '<?php echo $currentChat['type']; ?>'
        };
        const currentUser = {
            id: <?php echo $currentUser['id']; ?>,
            name: '<?php echo addslashes($currentUser['fname'] . ' ' . $currentUser['lname']); ?>',
            role: '<?php echo $currentUser['user_type']; ?>',
            avatar: '<?php echo strtoupper(substr($currentUser['fname'], 0, 1)); ?>'
        };
        let lastMessageId = <?php echo count($messages) > 0 ? end($messages)['id'] : 0; ?>;
        const chatTitle = '<?php echo htmlspecialchars($currentChat['name']); ?>';
        const participantsCount = <?php echo count($participants); ?>;
        const chatType = '<?php echo ucfirst($currentChat['type']); ?>';
    </script>
    
    <script src="assets/js/message.js"></script>
</body>

</html>