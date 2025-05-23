// Global variables
let searchTimeout = null;
const searchDelay = 500; // milliseconds

// Function to perform AJAX search
function performSearch() {
    const searchTerm = document.getElementById('searchInput').value;
    const userType = document.getElementById('userTypeSelect').value;
    const location = document.getElementById('locationInput').value;
    
    // Show loading indicator
    document.getElementById('loadingIndicator').style.display = 'block';
    
    // Construct the URL with query parameters
    const url = `user_management.php?ajax=get_users&search=${encodeURIComponent(searchTerm)}&user_type=${encodeURIComponent(userType)}&location=${encodeURIComponent(location)}`;
    
    // Fetch the data
    fetch(url)
        .then(response => response.json())
        .then(data => {
            updateUserTable(data);
            document.getElementById('loadingIndicator').style.display = 'none';
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('loadingIndicator').style.display = 'none';
        });
}

// Function to get user initials from name
function getUserInitials(fname, lname) {
    const firstInitial = fname && fname.length > 0 ? fname.charAt(0).toUpperCase() : '';
    const lastInitial = lname && lname.length > 0 ? lname.charAt(0).toUpperCase() : '';
    return firstInitial + lastInitial || 'U'; // Default to 'U' if no name
}

// Function to update the user table with search results
function updateUserTable(users) {
    const tableBody = document.querySelector('table tbody');
    tableBody.innerHTML = '';
    
    if (users.length === 0) {
        document.getElementById('noResultsMessage').style.display = 'block';
        tableBody.innerHTML = '<tr><td colspan="9" class="text-center">No users found.</td></tr>';
    } else {
        document.getElementById('noResultsMessage').style.display = 'none';
        
        users.forEach(user => {
            const row = document.createElement('tr');
            
            // Create profile picture HTML
            let profilePicHTML = '';
            if (user.profile_pic && user.profile_pic !== '') {
                // If user has a profile picture, show it
                profilePicHTML = `<img src="${escapeHtml(user.profile_pic)}" alt="Profile" class="user-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                  <div class="user-avatar-placeholder" style="display:none;">${getUserInitials(user.fname, user.lname)}</div>`;
            } else {
                // If no profile picture, show initials
                profilePicHTML = `<div class="user-avatar-placeholder">${getUserInitials(user.fname, user.lname)}</div>`;
            }
            
            row.innerHTML = `
                <td>${profilePicHTML}</td>
                <td>${escapeHtml(user.fname)}</td>
                <td>${escapeHtml(user.lname)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>${escapeHtml(user.phone)}</td>
                <td>${escapeHtml(user.location)}</td>
                <td>${escapeHtml(user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1))}</td>
                <td>
                    <span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-danger'}">
                        ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                    </span>
                </td>
                <td>
                    <button class="btn ${user.status === 'active' ? 'btn-danger' : 'btn-success'} status-btn" 
                            data-user-id="${user.id}" 
                            data-action="${user.status === 'active' ? 'deactivate' : 'activate'}">
                        ${user.status === 'active' ? 'Deactivate' : 'Activate'}
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
        
        // Add event listeners to the newly created buttons
        attachStatusButtonListeners();
    }
}

// Function to handle status change
function handleStatusChange(userId, action) {
    fetch(`user_management.php?ajax=update_status&action=${action}&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the user table
                performSearch();
            } else {
                alert('Failed to update user status');
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
        });
}

// Function to attach event listeners to status buttons
function attachStatusButtonListeners() {
    document.querySelectorAll('.status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const action = this.getAttribute('data-action');
            handleStatusChange(userId, action);
        });
    });
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Setup search input with debounce
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, searchDelay);
    });
    
    // Setup filters
    document.getElementById('userTypeSelect').addEventListener('change', performSearch);
    document.getElementById('locationInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, searchDelay);
    });
    
    // Prevent form submission (we're handling it with AJAX)
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    // Attach listeners to initial buttons
    attachStatusButtonListeners();
});