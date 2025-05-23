// Lightbox functionality
function createLightbox() {
    if (!document.getElementById('imageLightbox')) {
        const lightbox = document.createElement('div');
        lightbox.id = 'imageLightbox';
        lightbox.className = 'lightbox';

        const img = document.createElement('img');
        img.className = 'lightbox-image';

        const closeBtn = document.createElement('div');
        closeBtn.className = 'lightbox-close';
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.onclick = closeLightbox;

        lightbox.appendChild(img);
        lightbox.appendChild(closeBtn);

        // Close lightbox when clicking outside the image
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        document.body.appendChild(lightbox);
    }
}

// Open the lightbox with an image
function openLightbox(imgSrc) {
    const lightbox = document.getElementById('imageLightbox') || createLightbox();
    const img = lightbox.querySelector('.lightbox-image');
    img.src = imgSrc;

    // Wait for image to load then show lightbox
    img.onload = function() {
        lightbox.classList.add('active');
    };
}

// Close the lightbox
function closeLightbox() {
    const lightbox = document.getElementById('imageLightbox');
    if (lightbox) {
        lightbox.classList.remove('active');
    }
}

// Setup image click handlers
function setupImageClickHandlers() {
    document.querySelectorAll('.attachment-image').forEach(img => {
        img.addEventListener('click', function() {
            openLightbox(this.src);
        });
    });
}

// Initialize the chat system
document.addEventListener('DOMContentLoaded', function() {
    createLightbox();
    setupImageClickHandlers();
    setupEventListeners();
    autoResizeTextarea();
    scrollToBottom();
    
    // Set up observer for dynamically added images
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        // Use MutationObserver to detect when new messages are added
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    setupImageClickHandlers();
                }
            });
        });

        observer.observe(messagesContainer, {
            childList: true
        });
    }
    
    // Start polling for new messages
    setInterval(checkForNewMessages, 3000);
    
    // Add keyboard support for lightbox (Escape key to close)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
    
    // Handle page visibility change to pause/resume polling
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // Page is visible, resume normal polling
            checkForNewMessages();
        }
    });

    // Handle online/offline status
    window.addEventListener('online', function() {
        showNotification('Connection restored', 'success');
        checkForNewMessages();
    });

    window.addEventListener('offline', function() {
        showNotification('Connection lost', 'warning');
    });

    // Full screen handling - moved from PHP to JS
    setupFullScreenHandling();
});

// Full screen setup function
function setupFullScreenHandling() {
    // Prevent zoom and ensure full screen
    document.addEventListener('touchstart', function(event) {
        if (event.touches.length > 1) {
            event.preventDefault();
        }
    });

    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);

    // Ensure the interface fits perfectly on load
    window.addEventListener('load', function() {
        adjustViewportHeight();
    });

    // Handle orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(adjustViewportHeight, 100);
    });

    // Handle resize
    window.addEventListener('resize', adjustViewportHeight);
}

// Adjust viewport height for full screen
function adjustViewportHeight() {
    const vh = window.innerHeight;
    document.body.style.height = vh + 'px';
    const chatContainer = document.querySelector('.chat-container');
    if (chatContainer) {
        chatContainer.style.height = vh + 'px';
    }
}

function setupEventListeners() {
    const messageInput = document.getElementById('messageInput');

    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    messageInput.addEventListener('input', autoResizeTextarea);

    // Setup file upload handler
    const fileUpload = document.getElementById('fileUpload');
    if (fileUpload) {
        fileUpload.addEventListener('change', handleFileUpload);
    }
}

function autoResizeTextarea() {
    const textarea = document.getElementById('messageInput');
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
}

function switchTab(tabName) {
    // Special handling for private tab - only redirect if there are no private chats
    if (tabName === 'private') {
        const privateList = document.getElementById('private-list');
        const privateChats = privateList.querySelectorAll('.chat-item');

        // Only redirect to search if user clicks on the tab itself (not on a chat item)
        // AND there are no existing private chats
        if (!event.target.closest('.chat-item') && privateChats.length === 0) {
            window.location.href = 'user_search.php';
            return;
        }
    }

    // Regular tab switching for other tabs
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.chat-list').forEach(list => list.classList.remove('active'));

    // Add active class to selected tab and list
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(`${tabName}-list`).classList.add('active');
}

function selectChat(chatId, chatType) {
    // Update current chat
    currentChat.id = chatId;
    currentChat.type = chatType;

    // Redirect to new chat
    window.location.href = `message.php?chat_id=${chatId}`;
}

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const messageText = messageInput.value.trim();

    if (messageText) {
        // Show sending indicator
        showNotification('Sending message...', 'info');

        fetch('chat_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_message',
                    chat_id: currentChat.id,
                    message: messageText,
                    is_emergency: false
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    autoResizeTextarea();
                    showNotification('Message sent successfully!', 'success');
                    // Refresh messages immediately
                    setTimeout(checkForNewMessages, 500);
                } else {
                    showNotification('Failed to send message: ' + (data.error || 'Unknown error'), 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error sending message: ' + error.message, 'danger');
            });
    } else {
        showNotification('Please enter a message', 'warning');
    }
}

function checkForNewMessages() {
    const url = `chat_api.php?action=get_new_messages&chat_id=${currentChat.id}&last_message_id=${lastMessageId}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                data.messages.forEach(message => {
                    addNewMessage(message);
                    lastMessageId = Math.max(lastMessageId, parseInt(message.id));
                });
                scrollToBottom();
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
            // Don't show notification for polling errors to avoid spam
        });
}

function addNewMessage(message) {
    const container = document.getElementById('messagesContainer');
    const typingIndicator = document.getElementById('typingIndicator');

    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message';

    // Add own class if it's current user's message
    if (parseInt(message.user_id) === currentUser.id) {
        messageDiv.classList.add('own');
    }

    // Add emergency class if it's an emergency message
    if (message.is_emergency == '1' || message.is_emergency === true) {
        messageDiv.classList.add('emergency-message');
    }

    // Create avatar
    const avatarDiv = document.createElement('div');
    avatarDiv.className = 'message-avatar';

    // Set avatar image if available
    if (message.picture) {
        const avatarImg = document.createElement('img');
        avatarImg.src = message.picture;
        avatarImg.alt = 'User Avatar';
        avatarDiv.appendChild(avatarImg);
    } else {
        avatarDiv.textContent = message.fname ? message.fname.charAt(0).toUpperCase() : 'U';
    }

    // Create content container
    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';

    // Create header
    const headerDiv = document.createElement('div');
    headerDiv.className = 'message-header';

    // Create sender name
    const senderSpan = document.createElement('span');
    senderSpan.className = 'message-sender';
    senderSpan.textContent = `${message.fname || 'Unknown'} (${message.user_type || 'user'})`;

    // Create time
    const timeSpan = document.createElement('span');
    timeSpan.className = 'message-time';
    const messageTime = new Date(message.created_at);
    timeSpan.textContent = messageTime.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });

    // Create message text
    const textDiv = document.createElement('div');
    textDiv.className = 'message-text';
    textDiv.textContent = message.message;

    // Append header elements
    headerDiv.appendChild(senderSpan);
    headerDiv.appendChild(timeSpan);

    // Append text
    contentDiv.appendChild(headerDiv);
    contentDiv.appendChild(textDiv);

    // Handle attachment if present
    if (message.attachment_url) {
        const attachmentDiv = document.createElement('div');
        attachmentDiv.className = 'message-attachment';

        const fileExt = message.attachment_url.split('.').pop().toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
        const isPdf = fileExt === 'pdf';
        const isVideo = ['mp4', 'webm'].includes(fileExt);
        const isAudio = ['mp3', 'wav', 'ogg'].includes(fileExt);

        if (isImage) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'attachment-preview';

            const img = document.createElement('img');
            img.src = message.attachment_url;
            img.alt = 'Attached image';
            img.className = 'attachment-image';
            img.addEventListener('click', function() {
                openLightbox(this.src);
            });

            previewDiv.appendChild(img);
            attachmentDiv.appendChild(previewDiv);
        } else if (isPdf) {
            const fileDiv = document.createElement('div');
            fileDiv.className = 'attachment-file pdf-file';

            const icon = document.createElement('i');
            icon.className = 'fas fa-file-pdf';

            const link = document.createElement('a');
            link.href = message.attachment_url;
            link.target = '_blank';
            link.className = 'attachment-link';
            link.textContent = 'View PDF Document';

            fileDiv.appendChild(icon);
            fileDiv.appendChild(link);
            attachmentDiv.appendChild(fileDiv);
        } else if (isVideo) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'attachment-preview';

            const video = document.createElement('video');
            video.controls = true;
            video.className = 'attachment-video';

            const source = document.createElement('source');
            source.src = message.attachment_url;
            source.type = `video/${fileExt}`;

            video.appendChild(source);
            previewDiv.appendChild(video);
            attachmentDiv.appendChild(previewDiv);
        } else if (isAudio) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'attachment-preview';

            const audio = document.createElement('audio');
            audio.controls = true;
            audio.className = 'attachment-audio';

            const source = document.createElement('source');
            source.src = message.attachment_url;
            source.type = `audio/${fileExt}`;

            audio.appendChild(source);
            previewDiv.appendChild(audio);
            attachmentDiv.appendChild(previewDiv);
        } else {
            const fileDiv = document.createElement('div');
            fileDiv.className = 'attachment-file';

            const icon = document.createElement('i');
            icon.className = 'fas fa-file';

            const link = document.createElement('a');
            link.href = message.attachment_url;
            link.download = true;
            link.className = 'attachment-link';
            link.textContent = 'Download Attachment';

            fileDiv.appendChild(icon);
            fileDiv.appendChild(link);
            attachmentDiv.appendChild(fileDiv);
        }

        contentDiv.appendChild(attachmentDiv);
    }

    // Assemble the message
    messageDiv.appendChild(avatarDiv);
    messageDiv.appendChild(contentDiv);

    // Insert before typing indicator if it exists and is visible
    if (typingIndicator && typingIndicator.style.display !== 'none') {
        container.insertBefore(messageDiv, typingIndicator);
    } else {
        container.appendChild(messageDiv);
    }
}

function attachFile() {
    document.getElementById('fileUpload').click();
}

function showChatInfo() {
    alert(`Chat Info:
Name: ${chatTitle}
Participants: ${participantsCount}
Type: ${chatType}`);
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;

    // Set color based on type
    switch (type) {
        case 'success':
            notification.style.background = 'var(--success-color)';
            break;
        case 'warning':
            notification.style.background = 'var(--warning-color)';
            break;
        case 'danger':
            notification.style.background = 'var(--danger-color)';
            break;
        case 'info':
            notification.style.background = 'var(--info-color)';
            break;
    }

    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// File upload handler
function handleFileUpload(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (5MB limit)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
            showNotification(`File "${file.name}" exceeds the 5MB size limit`, 'warning');
            e.target.value = ''; // Reset the input
            return;
        }

        // Show file is being uploaded
        showNotification(`Uploading file "${file.name}"...`, 'info');

        // Get optional message
        const messageInput = document.getElementById('messageInput');
        const messageText = messageInput.value.trim();

        // Create FormData object
        const formData = new FormData();
        formData.append('file', file);
        formData.append('chat_id', currentChat.id);
        formData.append('message', messageText);

        // Upload the file
        fetch('file_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Clear the message input
                    messageInput.value = '';
                    autoResizeTextarea();

                    showNotification(`File "${file.name}" uploaded successfully!`, 'success');

                    // Create a new message object to immediately display the uploaded file
                    const currentUserAvatarImg = document.querySelector('#currentUserAvatar img');
                    const newMessage = {
                        id: data.message_id,
                        user_id: currentUser.id,
                        message: messageText || `Shared a file: ${file.name}`,
                        attachment_url: data.file_url,
                        created_at: new Date().toISOString(),
                        fname: currentUser.name.split(' ')[0],
                        lname: currentUser.name.split(' ').slice(1).join(' '),
                        user_type: currentUser.role,
                        picture: currentUserAvatarImg ? currentUserAvatarImg.src : '',
                        is_emergency: false
                    };

                    // Add the message to the UI immediately
                    addNewMessage(newMessage);
                    scrollToBottom();

                    // Also refresh messages from server
                    setTimeout(checkForNewMessages, 1000);
                } else {
                    showNotification(`Failed to upload file: ${data.error}`, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(`Error uploading file: ${error.message}`, 'danger');
            })
            .finally(() => {
                // Reset file input
                e.target.value = '';
            });
    }
}