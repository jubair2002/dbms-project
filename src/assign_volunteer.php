<?php
require_once 'config.php';
require_once 'chat_functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// Fetch all ongoing campaigns
$campaignsQuery = "SELECT * FROM campaigns WHERE status = 'approved' AND progress = 'ongoing'";
$campaignsResult = $conn->query($campaignsQuery);

if (!$campaignsResult) {
    die("Error fetching campaigns: " . $conn->error);
}

// Fetch all active volunteers
$volunteersQuery = "SELECT * FROM users WHERE user_type = 'volunteer' AND status = 'active'";
$volunteersResult = $conn->query($volunteersQuery);

if (!$volunteersResult) {
    die("Error fetching volunteers: " . $conn->error);
}

$allVolunteers = $volunteersResult->fetch_all(MYSQLI_ASSOC);

// Handle form submission (same as before)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_tasks'])) {
    // ... [keep all the existing form handling code exactly as is] ...
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Volunteer Assignment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ffffff;
            --secondary: #000000;
            --secondary-hover: #333333;
            --accent: #000000;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --border-radius: 0.375rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            padding: 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }

        .header {
            background: var(--primary);
            color: var(--secondary);
            padding: 1rem;
            text-align: center;
            border-bottom: 2px solid white;
            position: relative;
        }

        .header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--secondary);
        }
.top-right-btn {
    position: absolute;
    right: 1rem;
    top: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    background-color: var(--secondary);
    color: white;
    text-decoration: none;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-weight: 500;
    transition: var(--transition);
}

.top-right-btn:hover {
    background-color: var(--secondary-hover);
}

        .alert {
            padding: 1rem;
            margin: 1rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: #e6f7ee;
            color: #0d6832;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: #fce8e8;
            color: #9b2c2c;
            border-left: 4px solid var(--danger);
        }

        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .campaign-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
            transition: var(--transition);
        }

        .campaign-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 1.25rem;
        }

        .campaign-name {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--secondary);
        }

        .campaign-description {
            color: var(--gray);
            margin-bottom: 1rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            width: 100%;
        }

        .btn-primary {
            background-color: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background-color: #333333;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 2rem auto;
            max-width: 700px;
            width: 90%;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
        }

        .modal-header {
            padding: 1.25rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
        }

        .modal-body {
            padding: 1.25rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--secondary);
        }

        .required {
            color: var(--danger);
        }

        .volunteer-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .volunteer-card {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .volunteer-card:hover {
            border-color: var(--accent);
        }

        .volunteer-card.selected {
            border-color: var(--accent);
            background-color: #f0f0f0;
        }

        .volunteer-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }

        .volunteer-info {
            flex: 1;
        }

        .volunteer-name {
            font-weight: 500;
            margin-bottom: 0.125rem;
            font-size: 0.875rem;
        }

        .volunteer-email {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .task-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .remove-task {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
            outline: none;
        }

        textarea.form-control {
            min-height: 80px;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        .btn-add-task {
            background-color: white;
            border: 1px dashed var(--accent);
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .btn-add-task:hover {
            background-color: #f0f0f0;
        }

        .btn-submit {
            background-color: var(--accent);
            color: white;
            padding: 0.75rem;
            font-size: 1rem;
        }

        .btn-submit:hover {
            background-color: #333333;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        @media (max-width: 768px) {
            .campaigns-grid {
                grid-template-columns: 1fr;
            }

            .volunteer-list {
                grid-template-columns: 1fr;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .top-right-btn {
                position: relative;
                right: auto;
                top: auto;
                margin: 0.5rem auto;
                display: block;
                width: fit-content;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <button class="top-right-btn" onclick="location.href='campaignSummary.php'">
                <i class="fas fa-tachometer-alt"></i> Back To Campaign
            </button>
            <h1><i class="fas fa-user-friends"></i> Assign Volunteers to Campaigns</h1>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($campaignsResult->num_rows > 0): ?>
            <div class="campaigns-grid">
                <?php while ($campaign = $campaignsResult->fetch_assoc()): ?>
                    <div class="campaign-card">
                        <div class="card-body">
                            <h3 class="campaign-name"><?php echo htmlspecialchars($campaign['name']); ?></h3>
                            <p class="campaign-description">
                                <?php echo htmlspecialchars(substr($campaign['description'], 0, 120)) . '...'; ?>
                            </p>
                            <button class="btn btn-primary open-assign-modal"
                                data-campaign-id="<?php echo $campaign['id']; ?>"
                                data-campaign-name="<?php echo htmlspecialchars($campaign['name']); ?>">
                                <i class="fas fa-user-plus"></i> Assign Volunteer
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-check"></i>
                <h3>No Active Campaigns</h3>
                <p>There are currently no ongoing campaigns available for volunteer assignment.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Assignment Modal -->
    <div class="modal" id="assignModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Assign Volunteer</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="taskAssignmentForm">
                    <input type="hidden" id="campaign_id" name="campaign_id">

                    <div class="form-group">
                        <h4 class="section-title">Select Volunteer <span class="required">*</span></h4>
                        <?php if (!empty($allVolunteers)): ?>
                            <div class="volunteer-list">
                                <?php foreach ($allVolunteers as $volunteer): ?>
                                    <div class="volunteer-card" data-volunteer-id="<?php echo $volunteer['id']; ?>">
                                        <div class="volunteer-avatar">
                                            <?php echo strtoupper(substr($volunteer['fname'], 0, 1) . substr($volunteer['lname'], 0, 1)); ?>
                                        </div>
                                        <div class="volunteer-info">
                                            <div class="volunteer-name">
                                                <?php echo htmlspecialchars($volunteer['fname'] . ' ' . $volunteer['lname']); ?>
                                            </div>
                                            <div class="volunteer-email">
                                                <?php echo htmlspecialchars($volunteer['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--gray); font-style: italic;">No active volunteers available.</p>
                        <?php endif; ?>
                        <input type="hidden" id="volunteer_id" name="volunteer_id">
                    </div>

                    <div class="form-group">
                        <h4 class="section-title">Assign Tasks <span class="required">*</span></h4>
                        <div class="task-list" id="taskList">
                            <!-- Tasks will be added here by JavaScript -->
                        </div>

                        <button type="button" class="btn btn-add-task" id="addTaskBtn">
                            <i class="fas fa-plus"></i> Add Task
                        </button>
                    </div>

                    <button type="submit" class="btn btn-submit" name="assign_tasks">
                        <i class="fas fa-paper-plane"></i> Assign Tasks
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let taskCounter = 0;
        let selectedVolunteerId = null;

        // Modal functionality
        const modal = document.getElementById('assignModal');
        const closeModal = document.querySelector('.close-modal');

        // Open modal when assign button is clicked
        document.querySelectorAll('.open-assign-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                const campaignId = this.getAttribute('data-campaign-id');
                const campaignName = this.getAttribute('data-campaign-name');

                document.getElementById('campaign_id').value = campaignId;
                document.getElementById('modal-title').textContent = `Assign Volunteer: ${campaignName}`;

                // Reset form
                resetForm();

                // Add initial task
                addTask();

                modal.style.display = 'block';
            });
        });

        // Close modal
        closeModal.addEventListener('click', () => modal.style.display = 'none');
        window.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        // Volunteer selection
        document.querySelectorAll('.volunteer-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selection from all cards
                document.querySelectorAll('.volunteer-card').forEach(c => c.classList.remove('selected'));

                // Select current card
                this.classList.add('selected');

                selectedVolunteerId = this.getAttribute('data-volunteer-id');
                document.getElementById('volunteer_id').value = selectedVolunteerId;
            });
        });

        // Add task functionality
        document.getElementById('addTaskBtn').addEventListener('click', addTask);

        function addTask() {
            const taskList = document.getElementById('taskList');
            const taskItem = document.createElement('div');
            taskItem.className = 'task-item';
            taskItem.innerHTML = `
                <button type="button" class="remove-task" onclick="removeTask(this)">
                    <i class="fas fa-times"></i> Remove
                </button>
                <div class="form-group">
                    <label class="form-label">Task Name <span class="required">*</span></label>
                    <input type="text" class="form-control" name="tasks[${taskCounter}][name]" 
                           placeholder="Enter task name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Task Description <span class="required">*</span></label>
                    <textarea class="form-control" name="tasks[${taskCounter}][description]" 
                              placeholder="Describe the task details" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select class="form-control" name="tasks[${taskCounter}][priority]">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deadline</label>
                        <input type="datetime-local" class="form-control" 
                               name="tasks[${taskCounter}][deadline]" value="${getDefaultDeadline()}">
                    </div>
                </div>
            `;

            taskList.appendChild(taskItem);
            taskCounter++;
        }

        function removeTask(btn) {
            if (document.querySelectorAll('.task-item').length > 1) {
                btn.closest('.task-item').remove();
            } else {
                alert('At least one task is required.');
            }
        }

        function getDefaultDeadline() {
            const date = new Date();
            date.setDate(date.getDate() + 7); // 7 days from now
            return date.toISOString().slice(0, 16);
        }

        function resetForm() {
            selectedVolunteerId = null;
            document.getElementById('volunteer_id').value = '';
            document.querySelectorAll('.volunteer-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Clear tasks
            document.getElementById('taskList').innerHTML = '';
            taskCounter = 0;
        }

        // Form validation
        document.getElementById('taskAssignmentForm').addEventListener('submit', function(e) {
            if (!selectedVolunteerId) {
                e.preventDefault();
                alert('Please select a volunteer.');
                return false;
            }

            const tasks = document.querySelectorAll('.task-item');
            if (tasks.length === 0) {
                e.preventDefault();
                alert('Please add at least one task.');
                return false;
            }

            // Check if all required fields are filled
            let isValid = true;
            let emptyFields = [];

            tasks.forEach((task, index) => {
                const nameInput = task.querySelector('input[name*="[name]"]');
                const descInput = task.querySelector('textarea[name*="[description]"]');

                if (!nameInput.value.trim()) {
                    isValid = false;
                    emptyFields.push(`Task ${index + 1} name`);
                }

                if (!descInput.value.trim()) {
                    isValid = false;
                    emptyFields.push(`Task ${index + 1} description`);
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in the following required fields:\n' + emptyFields.join('\n'));
                return false;
            }

            // Show confirmation
            const taskCount = tasks.length;
            const volunteerName = document.querySelector('.volunteer-card.selected .volunteer-name').textContent;

            if (!confirm(`Are you sure you want to assign ${taskCount} task(s) to ${volunteerName}?`)) {
                e.preventDefault();
                return false;
            }

            return true;
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>