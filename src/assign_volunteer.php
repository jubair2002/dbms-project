<?php 
require_once 'config.php';

// Fetch all ongoing campaigns
$campaignsQuery = "SELECT * FROM campaigns WHERE status = 'approved' AND progress = 'ongoing'";
$campaignsResult = $conn->query($campaignsQuery);

// Fetch all active volunteers
$volunteersQuery = "SELECT * FROM users WHERE user_type = 'volunteer' AND status = 'active'";
$volunteersResult = $conn->query($volunteersQuery);
$allVolunteers = $volunteersResult->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_tasks'])) {
    $campaign_id = $_POST['campaign_id'];
    $volunteer_id = $_POST['volunteer_id'];
    $tasks = $_POST['tasks'];
    
    $conn->begin_transaction();
    
    try {
        $successCount = 0;
        
        foreach ($tasks as $task) {
            if (!empty($task['name'])) {
                // Insert into assignments
                $assignmentStmt = $conn->prepare("INSERT INTO assignments (campaign_id, volunteer_id, task_name, description, priority, deadline) 
                                                VALUES (?, ?, ?, ?, ?, ?)");
                $assignmentStmt->bind_param("iissss", $campaign_id, $volunteer_id, 
                                          $task['name'], $task['description'], 
                                          $task['priority'], $task['deadline']);
                
                if ($assignmentStmt->execute()) {
                    $assignment_id = $conn->insert_id;
                    
                    // Insert into tasks
                    $taskStmt = $conn->prepare("INSERT INTO tasks (assignment_id, task_name, description, priority, deadline) 
                                              VALUES (?, ?, ?, ?, ?)");
                    $taskStmt->bind_param("issss", $assignment_id, $task['name'], 
                                        $task['description'], $task['priority'], 
                                        $task['deadline']);
                    
                    if ($taskStmt->execute()) {
                        $successCount++;
                    } else {
                        throw new Exception("Task insertion failed: " . $taskStmt->error);
                    }
                } else {
                    throw new Exception("Assignment insertion failed: " . $assignmentStmt->error);
                }
            }
        }
        
        $conn->commit();
        $success_message = "Successfully assigned $successCount tasks!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Volunteer Assignment</title>
    <link rel="stylesheet" href="assets/css/assign_volunteer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tasks"></i> Campaign Volunteer Assignment</h1>
        
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="campaigns-grid">
            <?php while($campaign = $campaignsResult->fetch_assoc()): ?>
                <div class="campaign-card">
                    <h3 class="campaign-name"><?php echo htmlspecialchars($campaign['name']); ?></h3>
                    <p class="campaign-description"><?php echo htmlspecialchars(substr($campaign['description'], 0, 100)); ?>...</p>
                    <button class="assign-btn open-assign-modal" data-campaign-id="<?php echo $campaign['id']; ?>">
                        <i class="fas fa-user-plus"></i> Assign Volunteer
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Assignment Modal -->
    <div class="modal" id="assignModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modal-title">Assign Volunteer</h2>
            
            <div class="volunteer-selection">
                <h3>Select Volunteer</h3>
                <div class="volunteer-list">
                    <?php foreach($allVolunteers as $volunteer): ?>
                        <div class="volunteer-card" data-volunteer-id="<?php echo $volunteer['id']; ?>">
                            <div class="volunteer-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="volunteer-name">
                                <?php echo htmlspecialchars($volunteer['fname'] . ' ' . $volunteer['lname']); ?>
                            </div>
                            <div class="volunteer-email">
                                <?php echo htmlspecialchars($volunteer['email']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="task-form">
                <form id="taskAssignmentForm">
                    <input type="hidden" id="campaign_id" name="campaign_id">
                    <input type="hidden" id="volunteer_id" name="volunteer_id">
                    
                    <h3>Assign Tasks</h3>
                    <div class="task-list" id="taskList">
                        <!-- Tasks will be added here -->
                    </div>
                    
                    <button type="button" class="add-task-btn" id="addTaskBtn">
                        <i class="fas fa-plus"></i> Add Another Task
                    </button>
                    
                    <button type="submit" class="submit-tasks" name="assign_tasks">
                        <i class="fas fa-paper-plane"></i> Submit Assignments
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/assign_volunteer.js"></script>
</body>
</html>

<?php 
$conn->close(); 
?>