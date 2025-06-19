<?php
// Include the database configuration file
require_once 'config.php';  

// Check for admin authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: auth.php'); // Redirect to login if not admin
    exit();
}

// Fetch tasks with their associated assignments and volunteers
$sql = "SELECT tasks.id AS task_id, tasks.task_name, tasks.status AS task_status, tasks.deadline, 
               assignments.status AS assignment_status, users.fname, users.lname, users.id AS volunteer_id
        FROM tasks
        INNER JOIN assignments ON tasks.assignment_id = assignments.id
        INNER JOIN users ON assignments.volunteer_id = users.id
        WHERE assignments.status != 'completed'
        ORDER BY tasks.deadline ASC";  

$result = $conn->query($sql);

if ($result === false) {
    echo "Error: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Task Progress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/track-task-progress.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Track Task Progress</h1>
            <a href="campaignSummary.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>
        
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Volunteer</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    while ($task = $result->fetch_assoc()) { 
                        // Determine status class
                        $statusClass = 'status-' . str_replace(' ', '-', strtolower($task['task_status']));
                        
                        // Check if task is overdue
                        $isOverdue = date('Y-m-d') > date('Y-m-d', strtotime($task['deadline'])) && $task['task_status'] != 'completed';
                        $rowClass = $isOverdue ? 'overdue' : '';
                        $deadlineClass = $isOverdue ? 'deadline-overdue' : '';
                ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['fname'] . " " . $task['lname']); ?></td>
                        <td>
                            <span class="status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($task['task_status']); ?>
                            </span>
                        </td>
                        <td class="<?php echo $deadlineClass; ?>">
                            <?php echo htmlspecialchars($task['deadline']); ?>
                            <?php if ($isOverdue): ?>
                                <br><small><strong>OVERDUE</strong></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($task['task_status'] != 'completed'): ?>
                                <button class="btn-notify" 
                                        onclick="notifyVolunteer(<?php echo $task['volunteer_id']; ?>, <?php echo $task['task_id']; ?>, '<?php echo htmlspecialchars($task['fname'] . ' ' . $task['lname'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-bell"></i> Notify
                                </button>
                            <?php else: ?>
                                <span style="color: #28a745; font-weight: 500;">
                                    <i class="fas fa-check-circle"></i> Task Completed
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="5" class="no-tasks">No tasks to track at this time.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="assets/js/track-task-progress.js"></script>
</body>
</html>

<?php 
// Close the database connection
$conn->close(); 
?>