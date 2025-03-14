// adminDashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the dashboard by loading the dashboard content
    loadPage('dashboard.php');
    
    // Set up event listeners for window resize
    window.addEventListener('resize', function() {
        adjustContentHeight();
    });
    
    // Initial height adjustment
    adjustContentHeight();
});

// Function to load content via AJAX
function loadContent(url) {
    const contentArea = document.getElementById('contentArea');
    
    // Show loading spinner
    contentArea.innerHTML = '<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Make AJAX request
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Insert the HTML into the content area
            contentArea.innerHTML = html;
        })
        .catch(error => {
            contentArea.innerHTML = `<div class="alert alert-danger">Error loading content: ${error.message}</div>`;
            console.error('Error loading content:', error);
        });
}

// Function to load pages and update sidebar active state
function loadPage(page) {
    // Load the content
    loadContent(page);
    
    // Update active state in sidebar
    updateActiveSidebarItem(page);
    
    return false; // Prevent default link behavior
}

// Update active state in sidebar
function updateActiveSidebarItem(page) {
    // Remove active class from all sidebar links
    const sidebarLinks = document.querySelectorAll('.sidebar-list li a');
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
    });
    
    // Add active class to the clicked link
    const activeLink = document.querySelector(`.sidebar-list li a[onclick*="${page}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Function to adjust content area height based on viewport size
function adjustContentHeight() {
    const contentArea = document.getElementById('contentArea');
    
    if (window.innerWidth > 991) {
        // Desktop view
        contentArea.style.height = 'calc(100vh - 140px)';
    } else if (window.innerWidth > 767) {
        // Tablet view
        contentArea.style.height = 'calc(100vh - 300px)';
        contentArea.style.minHeight = '500px';
    } else {
        // Mobile view
        contentArea.style.height = 'calc(100vh - 270px)';
        contentArea.style.minHeight = '400px';
    }
}

// Function to mark notifications as read (if you need this functionality)
function markNotificationsAsRead() {
    fetch('mark_notifications_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update notification count badge
            document.getElementById('notification-count').textContent = '0';
        }
    })
    .catch(error => {
        console.error('Error marking notifications as read:', error);
    });
}