<?php  
// Include the database configuration file
require_once 'config.php';   
session_start();  

// Assuming the volunteer is logged in
$volunteer_id = $_SESSION['user_id'];  

// Fetch the tasks assigned to this volunteer
$sql = "SELECT tasks.id AS task_id, tasks.task_name, tasks.description, tasks.priority, tasks.deadline,
               tasks.status AS task_status, assignments.status AS assignment_status 
        FROM tasks
        INNER JOIN assignments ON tasks.assignment_id = assignments.id
        WHERE assignments.volunteer_id = ? AND assignments.status != 'completed'";

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
    <title>My Tasks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #ff0000;
            --light-color: #ffffff;
            --gray-color: #cccccc;
            --border-radius: 4px;
            --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
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
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid var(--accent-color);
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .task-table {
            width: 100%;
            border-collapse: collapse;
        }

        .task-table thead {
            background-color: #f0f0f0;
        }

        .task-table th, .task-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-color);
        }

        .task-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            color: var(--primary-color);
        }

        .task-table tr:hover {
            background-color: #f9f9f9;
        }

        .priority-low {
            color: #27ae60;
        }

        .priority-medium {
            color: #f39c12;
        }

        .priority-high {
            color: var(--accent-color);
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: var(--border-radius);
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-not-started {
            background-color: #e0e0e0;
            color: var(--secondary-color);
        }

        .status-assigned {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-in-progress {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-complete {
            background-color: #28a745;
            color: white;
        }

        .btn-complete:hover {
            background-color: #218838;
        }

        .btn-update {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-update:hover {
            background-color: #cc0000;
        }

        .no-tasks {
            text-align: center;
            padding: 30px;
            color: var(--secondary-color);
            font-style: italic;
        }

        @media (max-width: 768px) {
            .task-table {
                display: block;
                overflow-x: auto;
            }

            .task-table thead {
                display: none;
            }

            .task-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid var(--gray-color);
                border-radius: var(--border-radius);
            }

            .task-table td {
                display: block;
                text-align: right;
                padding: 10px 15px;
                border-bottom: 1px solid var(--gray-color);
            }

            .task-table td::before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                text-transform: uppercase;
                color: var(--primary-color);
            }

            .task-table td:last-child {
                border-bottom: none;
            }

            .action-buttons {
                justify-content: flex-end;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .header {
                padding: 15px;
            }

            .header h1 {
                font-size: 20px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .btn {
                justify-content: center;
                padding: 8px;
            }
        }
    </style>
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
                                <a href="update_task.php?task_id=<?php echo $task['task_id']; ?>&status=in-progress" 
                                   class="btn btn-update">
                                    <i class="fas fa-sync-alt"></i> Update
                                </a>
                                <a href="update_task.php?task_id=<?php echo $task['task_id']; ?>&status=completed" 
                                   class="btn btn-complete">
                                    <i class="fas fa-check"></i> Complete
                                </a>
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
</body>
</html>

<?php  
// Close the database connection
$conn->close();  
?>