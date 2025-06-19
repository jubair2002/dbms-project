<?php  
// Include the database configuration file
require_once 'config.php';   
session_start();  

// Security check - ensure volunteer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'volunteer') {
    header('Location: auth.php');
    exit();
}

$volunteer_id = $_SESSION['user_id'];  

// Fetch all tasks assigned to this volunteer, regardless of completion status
$sql = "SELECT tasks.id AS task_id, tasks.task_name, tasks.description, tasks.priority, tasks.deadline,
               tasks.status AS task_status, assignments.status AS assignment_status 
        FROM tasks
        INNER JOIN assignments ON tasks.assignment_id = assignments.id
        WHERE assignments.volunteer_id = ? 
        ORDER BY tasks.deadline ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - Volunteer Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/assignments.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tasks"></i> My Tasks</h1>
        </div>
        
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    while ($task = $result->fetch_assoc()) { 
                        $priorityClass = 'priority-' . strtolower($task['priority']);
                        $statusClass = 'status-' . str_replace(' ', '-', strtolower($task['task_status']));
                        $isCompleted = $task['task_status'] == 'completed';
                ?>
                    <tr>
                        <td data-label="Task Name"><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td data-label="Description"><?php echo htmlspecialchars($task['description']); ?></td>
                        <td data-label="Priority" class="<?php echo $priorityClass; ?>">
                            <?php echo htmlspecialchars($task['priority']); ?>
                        </td>
                        <td data-label="Deadline"><?php echo htmlspecialchars($task['deadline']); ?></td>
                        <td data-label="Status">
                            <span class="status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($task['task_status']); ?>
                            </span>
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <?php if (!$isCompleted): ?>
                                    <?php if ($task['task_status'] == 'assigned'): ?>
                                        <button class="btn btn-update" 
                                                onclick="startTask(<?php echo $task['task_id']; ?>, this)">
                                            <i class="fas fa-play"></i> Start Task
                                        </button>
                                    <?php elseif ($task['task_status'] == 'in-progress'): ?>
                                        <span class="btn btn-update" style="background-color: #17a2b8; cursor: default;">
                                            <i class="fas fa-sync-alt fa-spin"></i> In Progress
                                        </span>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-complete" 
                                            onclick="completeTask(<?php echo $task['task_id']; ?>, this)">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                <?php else: ?>
                                    <button class="btn" disabled>
                                        <i class="fas fa-check-circle"></i> Completed
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="6" class="no-tasks">No tasks assigned to you at this time.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="assets/js/assignments.js"></script>
</body>
</html>

<?php  
// Close the database connection
$conn->close();  
?>