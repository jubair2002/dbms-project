function notifyVolunteer(volunteerId, taskId, volunteerName) {
    if (confirm(`Send notification to ${volunteerName} about their pending task?`)) {
        // Store button reference
        const button = event.target;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        fetch('send_notification.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({volunteer_id: volunteerId, task_id: taskId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.innerHTML = '<i class="fas fa-check"></i> Sent';
                alert(`Notification sent to ${volunteerName} successfully!`);
            } else {
                button.innerHTML = '<i class="fas fa-bell"></i> Notify';
                alert('Failed to send notification: ' + data.message);
            }
            button.disabled = false;
        })
        .catch(error => {
            button.innerHTML = '<i class="fas fa-bell"></i> Notify';
            button.disabled = false;
            alert('Error sending notification');
        });
    }
}