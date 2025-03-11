document.addEventListener('DOMContentLoaded', function () {
    var notificationsBtn = document.getElementById('notificationsBtn');
    var notificationList = document.getElementById('notificationList');

    notificationsBtn.addEventListener('click', function () {
        notificationList.classList.toggle('show');
    });
});
document.getElementById('markAllAsReadBtn').addEventListener('click', function() {
    // Send AJAX request to mark all notifications as read
    fetch('mark_all_notifications_read.php', {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            document.getElementById('notification-count').innerText = '0'; // Reset the notification count to 0
            const unreadNotifications = document.querySelectorAll('.unread');
            unreadNotifications.forEach(function(notification) {
                notification.classList.remove('unread');
                notification.classList.add('read');
            });
        }
    });
});
