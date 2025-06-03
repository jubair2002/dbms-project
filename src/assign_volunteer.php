<?php
require_once 'config.php';
require_once 'chat_functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_tasks'])) {
    $campaign_id = intval($_POST['campaign_id']);
    $volunteer_id = intval($_POST['volunteer_id']);

    // Validate inputs
    if ($campaign_id <= 0 || $volunteer_id <= 0) {
        $error_message = "Invalid campaign or volunteer selection.";
    } else {
        // Check if tasks array exists and is not empty
        $tasks = isset($_POST['tasks']) ? $_POST['tasks'] : [];

        if (empty($tasks)) {
            $error_message = "Please add at least one task to assign.";
        } else {
            $conn->begin_transaction();

            try {
                $successCount = 0;

                foreach ($tasks as $task) {
                    if (!empty($task['name']) && !empty($task['description'])) {
                        // Set default values if not provided
                        $priority = isset($task['priority']) && !empty($task['priority']) ? $task['priority'] : 'medium';
                        $deadline = isset($task['deadline']) && !empty($task['deadline']) ? $task['deadline'] : date('Y-m-d H:i:s', strtotime('+7 days'));

                        // Insert into assignments table
                        $assignmentStmt = $conn->prepare("INSERT INTO assignments (campaign_id, volunteer_id, task_name, description, priority, deadline, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'assigned', NOW())");

                        if (!$assignmentStmt) {
                            throw new Exception("Prepare failed for assignments: " . $conn->error);
                        }

                        $assignmentStmt->bind_param("iissss", $campaign_id, $volunteer_id, $task['name'], $task['description'], $priority, $deadline);

                        if ($assignmentStmt->execute()) {
                            $assignment_id = $conn->insert_id;

                            // Insert into tasks table
                            $taskStmt = $conn->prepare("INSERT INTO tasks (assignment_id, task_name, description, priority, deadline, status, created_at) VALUES (?, ?, ?, ?, ?, 'assigned', NOW())");

                            if (!$taskStmt) {
                                throw new Exception("Prepare failed for tasks: " . $conn->error);
                            }

                            $taskStmt->bind_param("issss", $assignment_id, $task['name'], $task['description'], $priority, $deadline);

                            if ($taskStmt->execute()) {
                                $successCount++;
                            } else {
                                throw new Exception("Task insertion failed: " . $taskStmt->error);
                            }
                            $taskStmt->close();
                        } else {
                            throw new Exception("Assignment insertion failed: " . $assignmentStmt->error);
                        }
                        $assignmentStmt->close();
                    }
                }

                if ($successCount > 0) {
                    $conn->commit();
                    $success_message = "Successfully assigned $successCount task(s) to volunteer!";

                    // Get campaign name for notification
                    $campaignQuery = $conn->prepare("SELECT name FROM campaigns WHERE id = ?");
                    $campaignQuery->bind_param("i", $campaign_id);
                    $campaignQuery->execute();
                    $campaignResult = $campaignQuery->get_result();
                    $campaignData = $campaignResult->fetch_assoc();
                    $campaignName = $campaignData['name'];
                    $campaignQuery->close();

                    // Send notification to volunteer
                    $notificationStmt = $conn->prepare("INSERT INTO notifications (recipient_id, sender_id, title, message, entity_type, entity_id) VALUES (?, ?, ?, ?, 'assignment', ?)");
                    $notificationTitle = "New Task Assignment";
                    $notificationMessage = "You have been assigned to " . $successCount . " new task(s) in campaign: " . $campaignName;
                    $notificationStmt->bind_param("iissi", $volunteer_id, $_SESSION['user_id'], $notificationTitle, $notificationMessage, $assignment_id);

                    if (!$notificationStmt->execute()) {
                        // Log error but don't fail the whole process
                        error_log("Failed to send notification: " . $notificationStmt->error);
                    }
                    $notificationStmt->close();

                    // Handle chat system integration
                    try {
                        $chatSystem = new ChatSystem($conn);
                        $adminId = $_SESSION['user_id'];

                        // Check if campaign chat already exists
                        $chatQuery = "SELECT id FROM chat_rooms WHERE campaign_id = ? AND type = 'campaign' LIMIT 1";
                        $chatStmt = $conn->prepare($chatQuery);

                        if ($chatStmt) {
                            $chatStmt->bind_param("i", $campaign_id);
                            $chatStmt->execute();
                            $chatResult = $chatStmt->get_result();

                            if ($chatResult->num_rows > 0) {
                                // Chat exists, get the chat ID
                                $chatRow = $chatResult->fetch_assoc();
                                $campaignChatId = $chatRow['id'];
                            } else {
                                // Create new campaign chat
                                $campaignChatId = $chatSystem->createCampaignChat($campaign_id, $adminId);
                            }

                            if ($campaignChatId) {
                                // Add volunteer to the chat
                                $chatSystem->addParticipant($campaignChatId, $volunteer_id);

                                // Get volunteer name for welcome message
                                $volunteerQuery = "SELECT CONCAT(fname, ' ', lname) as full_name FROM users WHERE id = ?";
                                $volStmt = $conn->prepare($volunteerQuery);
                                $volStmt->bind_param("i", $volunteer_id);
                                $volStmt->execute();
                                $volResult = $volStmt->get_result();
                                $volunteerName = $volResult->fetch_assoc()['full_name'];
                                $volStmt->close();

                                // Send welcome message
                                $welcomeMessage = "Volunteer $volunteerName has been assigned to this campaign with $successCount task(s).";
                                $chatSystem->sendMessage($campaignChatId, $adminId, $welcomeMessage, false);

                                $success_message .= " Volunteer has been added to the campaign chat group.";
                            }
                            $chatStmt->close();
                        }
                    } catch (Exception $chatEx) {
                        // Log chat error but don't fail the whole process
                        error_log("Chat system error: " . $chatEx->getMessage());
                        $success_message .= " (Note: Tasks assigned successfully, but there was an issue with chat integration)";
                    }
                } else {
                    $conn->rollback();
                    $error_message = "No valid tasks were provided for assignment.";
                }
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
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
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #ff0000;
            --light-color: #ffffff;
            --gray-color: #cccccc;
            --success-color: #28a745;
            --error-color: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--primary-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .header {
            background: var(--primary-color);
            color: var(--light-color);
            padding: 30px 20px;
            text-align: center;
            border-bottom: 3px solid var(--accent-color);
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .alert {
            padding: 15px 20px;
            margin: 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .campaign-card {
            background: var(--light-color);
            border: 1px solid var(--gray-color);
            border-radius: var(--border-radius);
            padding: 20px;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
        }

        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .campaign-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .campaign-description {
            color: var(--secondary-color);
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .assign-btn {
            background: var(--accent-color);
            color: var(--light-color);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
        }

        .assign-btn:hover {
            background: #cc0000;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: var(--light-color);
            margin: 2% auto;
            padding: 20px;
            width: 90%;
            max-width: 700px;
            border-radius: var(--border-radius);
            max-height: 90%;
            overflow-y: auto;
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: var(--gray-color);
            line-height: 1;
        }

        .close-modal:hover {
            color: var(--accent-color);
        }

        .modal h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .volunteer-selection {
            margin: 20px 0;
        }

        .volunteer-selection h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .volunteer-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--gray-color);
            border-radius: var(--border-radius);
            background: #f8f9fa;
        }

        .volunteer-card {
            padding: 10px 15px;
            border-bottom: 1px solid var(--gray-color);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .volunteer-card:hover {
            background: #e9ecef;
        }

        .volunteer-card.selected {
            background: var(--accent-color);
            color: var(--light-color);
        }

        .volunteer-card:last-child {
            border-bottom: none;
        }

        .volunteer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light-color);
            font-size: 16px;
        }

        .volunteer-info {
            flex: 1;
        }

        .volunteer-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .volunteer-email {
            font-size: 12px;
            opacity: 0.8;
        }

        .task-form {
            margin-top: 20px;
        }

        .task-form h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .task-item {
            border: 1px solid var(--gray-color);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            position: relative;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.1);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .remove-task {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--error-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
        }

        .remove-task:hover {
            background: #c82333;
        }

        .add-task-btn,
        .submit-tasks {
            background: var(--accent-color);
            color: var(--light-color);
            border: none;
            padding: 12px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-task-btn:hover {
            background: #cc0000;
        }

        .submit-tasks {
            background: var(--success-color);
            width: 100%;
            margin-top: 20px;
            justify-content: center;
            font-size: 16px;
            padding: 15px;
        }

        .submit-tasks:hover {
            background: #218838;
        }

        .required {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .campaigns-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 15px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tasks"></i> Campaign Volunteer Assignment</h1>
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
                        <h3 class="campaign-name"><?php echo htmlspecialchars($campaign['name']); ?></h3>
                        <p class="campaign-description">
                            <?php echo htmlspecialchars(substr($campaign['description'], 0, 120)) . '...'; ?>
                        </p>
                        <button class="assign-btn open-assign-modal"
                            data-campaign-id="<?php echo $campaign['id']; ?>"
                            data-campaign-name="<?php echo htmlspecialchars($campaign['name']); ?>">
                            <i class="fas fa-user-plus"></i> Assign Volunteer
                        </button>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h3>No Active Campaigns</h3>
                <p>There are no ongoing campaigns available for volunteer assignment.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Assignment Modal -->
    <div class="modal" id="assignModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modal-title">Assign Volunteer to Campaign</h2>

            <form method="POST" id="taskAssignmentForm">
                <input type="hidden" id="campaign_id" name="campaign_id">

                <div class="volunteer-selection">
                    <h3>Select Volunteer <span class="required">*</span></h3>
                    <?php if (!empty($allVolunteers)): ?>
                        <div class="volunteer-list">
                            <?php foreach ($allVolunteers as $volunteer): ?>
                                <div class="volunteer-card" data-volunteer-id="<?php echo $volunteer['id']; ?>">
                                    <div class="volunteer-avatar">
                                        <i class="fas fa-user"></i>
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
                        <p style="color: #666; font-style: italic;">No active volunteers available.</p>
                    <?php endif; ?>
                    <input type="hidden" id="volunteer_id" name="volunteer_id">
                </div>

                <div class="task-form">
                    <h3>Assign Tasks <span class="required">*</span></h3>
                    <div class="task-list" id="taskList">
                        <!-- Tasks will be added here by JavaScript -->
                    </div>

                    <button type="button" class="add-task-btn" id="addTaskBtn">
                        <i class="fas fa-plus"></i> Add Another Task
                    </button>

                    <button type="submit" class="submit-tasks" name="assign_tasks">
                        <i class="fas fa-paper-plane"></i> Assign Tasks to Volunteer
                    </button>
                </div>
            </form>
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
                document.getElementById('modal-title').textContent = `Assign Volunteer to: ${campaignName}`;

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
                    <i class="fas fa-times"></i>
                </button>
                <div class="form-group">
                    <label for="task_name_${taskCounter}">Task Name <span class="required">*</span></label>
                    <input type="text" id="task_name_${taskCounter}" name="tasks[${taskCounter}][name]" 
                           placeholder="Enter task name" required>
                </div>
                <div class="form-group">
                    <label for="task_desc_${taskCounter}">Task Description <span class="required">*</span></label>
                    <textarea id="task_desc_${taskCounter}" name="tasks[${taskCounter}][description]" 
                              rows="3" placeholder="Describe the task details" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="task_priority_${taskCounter}">Priority</label>
                        <select id="task_priority_${taskCounter}" name="tasks[${taskCounter}][priority]">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="task_deadline_${taskCounter}">Deadline</label>
                        <input type="datetime-local" id="task_deadline_${taskCounter}" 
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