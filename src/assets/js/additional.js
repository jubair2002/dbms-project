document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('assignModal');
    const openModalButtons = document.querySelectorAll('.open-assign-modal');
    const closeModal = document.querySelector('.close-modal');
    const volunteerCards = document.querySelectorAll('.volunteer-card');
    const addTaskBtn = document.getElementById('addTaskBtn');
    const taskList = document.getElementById('taskList');
    const taskForm = document.getElementById('taskAssignmentForm');
    
    let selectedVolunteer = null;
    let selectedCampaign = null;
    
    // Open modal when assign button is clicked
    openModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectedCampaign = this.getAttribute('data-campaign-id');
            document.getElementById('campaign_id').value = selectedCampaign;
            
            // Update modal title with campaign name
            const campaignName = this.closest('.campaign-card').querySelector('.campaign-name').textContent;
            document.getElementById('modal-title').textContent = `Assign Volunteer to ${campaignName}`;
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close modal
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetForm();
    });
    
    // Volunteer selection
    volunteerCards.forEach(card => {
        card.addEventListener('click', function() {
            volunteerCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            selectedVolunteer = this.getAttribute('data-volunteer-id');
            document.getElementById('volunteer_id').value = selectedVolunteer;
        });
    });
    
    // Add task
    addTaskBtn.addEventListener('click', function() {
        const taskId = Date.now();
        const taskHtml = `
            <div class="task-item" data-task-id="${taskId}">
                <button type="button" class="remove-task" onclick="this.closest('.task-item').remove()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="form-group">
                    <label>Task Name</label>
                    <input type="text" name="tasks[${taskId}][name]" required>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="tasks[${taskId}][priority]" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="tasks[${taskId}][deadline]" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="tasks[${taskId}][description]" required></textarea>
                </div>
            </div>
        `;
        taskList.insertAdjacentHTML('beforeend', taskHtml);
    });
    
    // Form submission
    taskForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!selectedVolunteer) {
            alert('Please select a volunteer first');
            return;
        }
        
        if (taskList.children.length === 0) {
            alert('Please add at least one task');
            return;
        }
        
        this.submit();
    });
    
    // Reset form when modal closes
    function resetForm() {
        selectedVolunteer = null;
        taskList.innerHTML = '';
        volunteerCards.forEach(card => card.classList.remove('selected'));
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            resetForm();
        }
    });
    
    // Add first task automatically when volunteer is selected
    volunteerCards.forEach(card => {
        card.addEventListener('click', function() {
            if (taskList.children.length === 0) {
                addTaskBtn.click();
            }
        });
    });
});