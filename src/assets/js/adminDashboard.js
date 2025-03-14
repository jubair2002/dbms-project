// Function to load profile page in iframe
function loadProfile() {
    const profileIframe = document.getElementById('profileIframe');
    const notificationsIframe = document.getElementById('notificationsIframe');
    const pageIframe = document.getElementById('pageIframe');

    // Hide all iframes initially
    profileIframe.style.display = 'none';
    notificationsIframe.style.display = 'none';
}

// Function to load notifications page in iframe
function loadNotifications() {
    const profileIframe = document.getElementById('profileIframe');
    const notificationsIframe = document.getElementById('notificationsIframe');
    const pageIframe = document.getElementById('pageIframe');

    // Hide all iframes initially
    profileIframe.style.display = 'none';
    notificationsIframe.style.display = 'block';
}
