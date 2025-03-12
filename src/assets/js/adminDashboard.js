// Function to load the respective page in the iframe
function loadPage(page) {
    const profileIframe = document.getElementById('profileIframe');
    const notificationsIframe = document.getElementById('notificationsIframe');
    const pageIframe = document.getElementById('pageIframe');

    // Hide all iframes initially
    profileIframe.style.display = 'none';
    notificationsIframe.style.display = 'none';
    pageIframe.style.display = 'none';

    // Set the source of the iframe to the selected page and show it
    pageIframe.src = page;
    pageIframe.style.display = 'block';
}

// Function to load the profile page in iframe
function loadProfile() {
    const profileIframe = document.getElementById('profileIframe');
    const notificationsIframe = document.getElementById('notificationsIframe');
    const pageIframe = document.getElementById('pageIframe');

    // Hide all iframes initially
    profileIframe.style.display = 'none';
    notificationsIframe.style.display = 'none';
    pageIframe.style.display = 'none';

    // Show the profile iframe
    profileIframe.style.display = 'block';
}

// Function to load notifications page in iframe
function loadNotifications() {
    const profileIframe = document.getElementById('profileIframe');
    const notificationsIframe = document.getElementById('notificationsIframe');
    const pageIframe = document.getElementById('pageIframe');

    // Hide all iframes initially
    profileIframe.style.display = 'none';
    notificationsIframe.style.display = 'none';
    pageIframe.style.display = 'none';

    // Show the notifications iframe
    notificationsIframe.style.display = 'block';
}

// Update iframe heights on window resize
window.addEventListener('resize', function() {
    adjustIframeHeights();
});

// Set initial iframe heights on page load
document.addEventListener('DOMContentLoaded', function() {
    adjustIframeHeights();
});

// Function to adjust iframe heights based on window size
function adjustIframeHeights() {
    const profileIframe = document.getElementById('profileIframe');
    const notificationsIframe = document.getElementById('notificationsIframe');
    const pageIframe = document.getElementById('pageIframe');
    
    if (window.innerWidth > 991) {
        // Desktop view
        const height = 'calc(100vh - 140px)';
        profileIframe.style.height = height;
        notificationsIframe.style.height = height;
        pageIframe.style.height = height;
    } else if (window.innerWidth > 767) {
        // Tablet view
        const height = 'calc(100vh - 300px)';
        profileIframe.style.height = height;
        notificationsIframe.style.height = height;
        pageIframe.style.height = height;
        profileIframe.style.minHeight = '500px';
        notificationsIframe.style.minHeight = '500px';
        pageIframe.style.minHeight = '500px';
    } else {
        // Mobile view
        const height = 'calc(100vh - 270px)';
        profileIframe.style.height = height;
        notificationsIframe.style.height = height;
        pageIframe.style.height = height;
        profileIframe.style.minHeight = '400px';
        notificationsIframe.style.minHeight = '400px';
        pageIframe.style.minHeight = '400px';
    }
}