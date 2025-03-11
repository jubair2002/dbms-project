// Function to load profile page in iframe
function loadProfile() {
    var profileIframe = document.getElementById('profileIframe');
    var notificationsIframe = document.getElementById('notificationsIframe');

    // Show the profile iframe and hide notifications iframe
    profileIframe.style.display = 'block';
    notificationsIframe.style.display = 'none';
}

// Function to load notifications page in iframe
function loadNotifications() {
    var profileIframe = document.getElementById('profileIframe');
    var notificationsIframe = document.getElementById('notificationsIframe');

    // Show the notifications iframe and hide profile iframe
    profileIframe.style.display = 'none';
    notificationsIframe.style.display = 'block';
}
