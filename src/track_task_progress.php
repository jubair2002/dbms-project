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
               assignments.status AS assignment_status, users.fname, users.lname
        FROM tasks
        INNER JOIN assignments ON tasks.assignment_id = assignments.id
        INNER JOIN users ON assignments.volunteer_id = users.id
        WHERE assignments.status != 'completed'";  // Fetch tasks that are not completed

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

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: var(--border-radius);
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-assigned {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-in-progress {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-link:hover {
            text-decoration: underline;
            color: #cc0000;
        }

        .no-tasks {
            text-align: center;
            padding: 30px;
            color: var(--secondary-color);
            font-style: italic;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                border-radius: 0;
            }

            .task-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .task-table th, .task-table td {
                padding: 8px 10px;
                font-size: 14px;
            }

            .header {
                padding: 15px;
            }

            .header h1 {
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .task-table th, .task-table td {
                padding: 6px 8px;
                font-size: 13px;
            }

            .status {
                padding: 3px 6px;
                font-size: 11px;
            }

            .action-link {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tasks"></i> Track Task Progress</h1>
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
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['fname'] . " " . $task['lname']); ?></td>
                        <td>
                            <span class="status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($task['task_status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                        <td>
                            <a href="mark_task_completed.php?task_id=<?php echo $task['task_id']; ?>" class="action-link">
                                <i class="fas fa-check"></i> Complete
                            </a>
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
</body>
</html>

<?php 
// Close the database connection
$conn->close(); 
?>