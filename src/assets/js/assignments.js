// ===== TASK MANAGEMENT FUNCTIONS =====

/**
 * Start a task and update status to in-progress
 * @param {number} taskId - The ID of the task to start
 * @param {HTMLElement} buttonElement - The button element that was clicked
 */
function startTask(taskId, buttonElement) {
    if (confirm('Are you sure you want to start this task?')) {
        // Show loading state
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';
        
        // Send AJAX request
        fetch('update_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${taskId}&status=in-progress`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button to show "In Progress"
                buttonElement.style.backgroundColor = '#17a2b8';
                buttonElement.style.cursor = 'default';
                buttonElement.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> In Progress';
                
                // Update status in the table
                const statusCell = buttonElement.closest('tr').querySelector('.status');
                statusCell.className = 'status status-in-progress';
                statusCell.textContent = 'in-progress';
                
                // Show success message
                showMessage('Task started successfully!', 'success');
            } else {
                // Reset button on error
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-play"></i> Start Task';
                showMessage('Error starting task: ' + data.message, 'error');
            }
        })
        .catch(error => {
            // Reset button on error
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<i class="fas fa-play"></i> Start Task';
            showMessage('Network error. Please try again.', 'error');
        });
    }
}

/**
 * Complete a task and update status to completed
 * @param {number} taskId - The ID of the task to complete
 * @param {HTMLElement} buttonElement - The button element that was clicked
 */
function completeTask(taskId, buttonElement) {
    if (confirm('Are you sure you want to mark this task as completed?')) {
        // Show loading state
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Completing...';
        
        // Send AJAX request
        fetch('update_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `task_id=${taskId}&status=completed`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button to show completed
                buttonElement.style.backgroundColor = '#6c757d';
                buttonElement.style.cursor = 'not-allowed';
                buttonElement.innerHTML = '<i class="fas fa-check-circle"></i> Completed';
                
                // Update status in the table
                const statusCell = buttonElement.closest('tr').querySelector('.status');
                statusCell.className = 'status status-completed';
                statusCell.textContent = 'completed';
                
                // Hide start button if it exists
                const startButton = buttonElement.closest('.action-buttons').querySelector('.btn-update');
                if (startButton) {
                    startButton.style.display = 'none';
                }
                
                // Show success message
                showMessage('Task completed successfully!', 'success');
            } else {
                // Reset button on error
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-check"></i> Complete';
                showMessage('Error completing task: ' + data.message, 'error');
            }
        })
        .catch(error => {
            // Reset button on error
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<i class="fas fa-check"></i> Complete';
            showMessage('Network error. Please try again.', 'error');
        });
    }
}

// ===== NOTIFICATION FUNCTIONS =====

/**
 * Send notification to volunteer (admin function)
 * @param {number} volunteerId - The ID of the volunteer
 * @param {number} taskId - The ID of the task
 * @param {string} volunteerName - The name of the volunteer
 */
function notifyVolunteer(volunteerId, taskId, volunteerName) {
    if (confirm(`Send notification to ${volunteerName} about their pending task?`)) {
        // For now, just show an alert. Later you can implement actual notification
        alert(`Notification sent to ${volunteerName}!\n\n(Notification functionality will be implemented later)`);
        
        // Disable the button temporarily to prevent spam clicking
        event.target.disabled = true;
        event.target.innerHTML = '<i class="fas fa-check"></i> Sent';
        
        // Re-enable after 3 seconds
        setTimeout(() => {
            event.target.disabled = false;
            event.target.innerHTML = '<i class="fas fa-bell"></i> Notify';
        }, 3000);
        
        // Here you would normally make an AJAX call to send the notification
        // Example:
        // fetch('send_notification.php', {
        //     method: 'POST',
        //     headers: {'Content-Type': 'application/json'},
        //     body: JSON.stringify({volunteer_id: volunteerId, task_id: taskId})
        // });
    }
}

// ===== UI UTILITY FUNCTIONS =====

/**
 * Display toast message to user
 * @param {string} message - The message to display
 * @param {string} type - The type of message ('success' or 'error')
 */
function showMessage(message, type) {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        background-color: ${type === 'success' ? '#28a745' : '#dc3545'};
    `;
    messageDiv.textContent = message;
    
    // Add animation style
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    // Add to page
    document.body.appendChild(messageDiv);
    
    // Remove after 3 seconds
    setTimeout(() => {
        messageDiv.remove();
        style.remove();
    }, 3000);
}

// ===== FILTER FUNCTIONS (for admin pages) =====

/**
 * Filter tasks based on status and other criteria
 */
function filterTasks() {
    const statusFilter = document.getElementById('statusFilter');
    const overdueFilter = document.getElementById('overdueFilter');
    
    if (!statusFilter || !overdueFilter) return; // Exit if filters don't exist
    
    const statusValue = statusFilter.value.toLowerCase();
    const overdueValue = overdueFilter.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr[data-status]');
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        let showRow = true;
        
        // Status filter
        if (statusValue && row.getAttribute('data-status') !== statusValue) {
            showRow = false;
        }
        
        // Overdue/Active filter
        if (overdueValue === 'overdue' && row.getAttribute('data-overdue') !== 'true') {
            showRow = false;
        } else if (overdueValue === 'active' && row.getAttribute('data-active') !== 'true') {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
        if (showRow) visibleCount++;
    });
    
    // Update no-tasks message if needed
    updateNoTasksMessage(visibleCount, rows.length);
}

/**
 * Clear all filters
 */
function clearFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const overdueFilter = document.getElementById('overdueFilter');
    
    if (statusFilter) statusFilter.value = '';
    if (overdueFilter) overdueFilter.value = '';
    
    filterTasks();
}

/**
 * Update the no-tasks message based on filter results
 * @param {number} visibleCount - Number of visible tasks
 * @param {number} totalCount - Total number of tasks
 */
function updateNoTasksMessage(visibleCount, totalCount) {
    const noTasksRow = document.querySelector('.no-tasks');
    if (noTasksRow && noTasksRow.closest('tr')) {
        const parent = noTasksRow.closest('tr');
        if (visibleCount === 0 && totalCount > 0) {
            parent.style.display = '';
            noTasksRow.innerHTML = 'No tasks match the current filters. Try adjusting your filter criteria.';
        } else if (visibleCount > 0) {
            parent.style.display = 'none';
        }
    }
}

// ===== PAGE INITIALIZATION =====

/**
 * Initialize page functionality when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter event listeners if they exist
    const statusFilter = document.getElementById('statusFilter');
    const overdueFilter = document.getElementById('overdueFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTasks);
    }
    
    if (overdueFilter) {
        overdueFilter.addEventListener('change', filterTasks);
    }
    
    // Add any other initialization code here
    console.log('Page loaded and scripts initialized');
});

// ===== FORM VALIDATION UTILITIES =====

/**
 * Validate form fields
 * @param {HTMLFormElement} form - The form to validate
 * @returns {boolean} - Whether the form is valid
 */
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    return isValid;
}

/**
 * Reset form validation styling
 * @param {HTMLFormElement} form - The form to reset
 */
function resetFormValidation(form) {
    const fields = form.querySelectorAll('input, select, textarea');
    fields.forEach(field => {
        field.style.borderColor = '';
    });
}