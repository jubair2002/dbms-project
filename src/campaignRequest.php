<?php
// Include the database configuration file
require_once 'config.php';

// Check if the user is logged in and is an admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: auth.php'); // Redirect to login page if the user is not admin
    exit;
}

// Fetch pending campaigns
$sql = "SELECT * FROM campaigns WHERE status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($sql);

// Update campaign status (approve or reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action']; // 'approve' or 'reject'
    $campaign_id = intval($_GET['id']);

    if ($action == 'approve') {
        $update_sql = "UPDATE campaigns SET status = 'approved' WHERE id = ?";
    } elseif ($action == 'reject') {
        $delete_sql = "DELETE FROM campaigns WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $campaign_id);
        if ($stmt->execute()) {
            header("Location: campaign.php"); // Redirect back to the admin page
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // If the action is approve, update the status
    if ($action == 'approve') {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $campaign_id);
        if ($stmt->execute()) {
            header("Location: campaign.php"); // Redirect back to the admin page
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Requests - Admin</title>
    <link rel="stylesheet" href="assets/css/campaign.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f5f7fa;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 1rem;
        }
        
        .header h1 {
            font-size: 2.2rem;
            color: var(--primary-color);
            position: relative;
        }
        
        .header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background-color: var(--secondary-color);
            border-radius: 2px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
        }
        
        .back-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.4);
        }
        
        .causes-list {
            list-style-type: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .cause-item {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }
        
        .cause-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .cause-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .cause-image-placeholder {
            width: 100%;
            height: 200px;
            background-color: #eaeaea;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #9e9e9e;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .cause-header {
            padding: 1.5rem 1.5rem 0.5rem;
        }
        
        .cause-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .cause-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            font-size: 0.85rem;
            color: var(--text-light);
        }
        
        .cause-meta div {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .cause-description {
            padding: 0 1.5rem;
            color: var(--text-light);
            font-size: 1rem;
            min-height: 4.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            margin-bottom: 1.5rem;
            flex: 1;
        }
        
        .action-buttons {
            display: flex;
            padding: 1.5rem;
            gap: 0.75rem;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
        }
        
        .btn {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .approve-btn {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .approve-btn:hover {
            background-color: #27ae60;
        }
        
        .reject-btn {
            background-color: var(--danger-color);
            color: white;
        }
        
        .reject-btn:hover {
            background-color: #c0392b;
        }
        
        .review-btn {
            background-color: var(--primary-color);
            color: white;
        }
        
        .review-btn:hover {
            background-color: #2980b9;
        }
        
        .no-campaigns {
            text-align: center;
            width: 100%;
            padding: 3rem 1rem;
            background-color: white;
            border-radius: 12px;
            font-size: 1.2rem;
            color: var(--text-light);
            box-shadow: var(--card-shadow);
            grid-column: 1 / -1;
        }

        /* Responsive styles */
        @media (max-width: 900px) {
            .causes-list {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .causes-list {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 1rem;
            }
            
            .cause-title {
                font-size: 1.2rem;
            }
            
            .cause-image,
            .cause-image-placeholder {
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Campaign Requests</h1>
            <a href="campaign.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>

        <ul class="causes-list">
            <?php
            // Check if there are pending campaigns
            if ($result->num_rows > 0) {
                // Loop through each pending campaign
                while ($row = $result->fetch_assoc()) {
            ?>
                <li class="cause-item">
                    <?php if (!empty($row['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="cause-image">
                    <?php else: ?>
                    <div class="cause-image-placeholder">
                        <i class="fas fa-image fa-2x"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="cause-header">
                        <h3 class="cause-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="cause-meta">
                            <div><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <p class="cause-description"><?php echo htmlspecialchars($row['description']); ?></p>

                    <div class="action-buttons">
                        <a href="campaignRequest.php?action=approve&id=<?php echo $row['id']; ?>" class="btn approve-btn">
                            <i class="fas fa-check"></i> Approve
                        </a>
                        <a href="campaignRequest.php?action=reject&id=<?php echo $row['id']; ?>" class="btn reject-btn">
                            <i class="fas fa-times"></i> Reject
                        </a>
                        <a href="campaign-details.php?id=<?php echo $row['id']; ?>" class="btn review-btn">
                            <i class="fas fa-search"></i> Review
                        </a>
                    </div>
                </li>
            <?php
                }
            } else {
                echo "<div class='no-campaigns'><i class='fas fa-info-circle' style='font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem; display: block;'></i> No pending campaigns at the moment.</div>";
            }
            ?>
        </ul>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>