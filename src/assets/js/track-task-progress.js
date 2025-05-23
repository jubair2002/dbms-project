function notifyVolunteer(volunteerId, taskId, volunteerName) {
    // Show confirmation dialog
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