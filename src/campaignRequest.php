<?php
require_once 'config.php';

// Check if the user is logged in and is an admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: auth.php');
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
            header("Location: campaignRequest.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    if ($action == 'approve') {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $campaign_id);
        if ($stmt->execute()) {
            header("Location: campaignRequest.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #ff0000;
            --light-color: #ffffff;
            --gray-color: #e0e0e0;
            --dark-gray: #666666;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: var(--primary-color);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent-color);
        }

        .header h1 {
            font-size: 24px;
            color: var(--primary-color);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: var(--primary-color);
            color: var(--light-color);
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
        }

        .back-btn:hover {
            background-color: var(--secondary-color);
        }

        .campaigns-list {
            list-style-type: none;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 0;
        }

        .campaign-item {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border: 1px solid var(--gray-color);
            display: flex;
            flex-direction: column;
        }

        .campaign-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .campaign-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .campaign-image-placeholder {
            width: 100%;
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark-gray);
        }

        .campaign-content {
            padding: 20px;
            flex: 1;
        }

        .campaign-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .campaign-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: var(--dark-gray);
        }

        .campaign-meta div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .campaign-description {
            color: var(--secondary-color);
            font-size: 14px;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .action-buttons {
            display: flex;
            padding: 15px;
            gap: 10px;
            background-color: #f9f9f9;
            border-top: 1px solid var(--gray-color);
        }

        .btn {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 10px;
            font-weight: 500;
            font-size: 14px;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            border: none;
        }

        .approve-btn {
            background-color: #28a745;
            color: var(--light-color);
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: var(--accent-color);
            color: var(--light-color);
        }

        .reject-btn:hover {
            background-color: #cc0000;
        }

        .review-btn {
            background-color: var(--primary-color);
            color: var(--light-color);
        }

        .review-btn:hover {
            background-color: var(--secondary-color);
        }

        .no-campaigns {
            text-align: center;
            width: 100%;
            padding: 40px 20px;
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            font-size: 16px;
            color: var(--dark-gray);
            box-shadow: var(--box-shadow);
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .campaigns-list {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                font-size: 20px;
            }

            .campaign-image,
            .campaign-image-placeholder {
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-list"></i> Campaign Requests</h1>
            <a href="campaign.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>

        <ul class="campaigns-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="campaign-item">
                        <?php if (!empty($row['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="campaign-image">
                        <?php else: ?>
                            <div class="campaign-image-placeholder">
                                <i class="fas fa-image fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="campaign-content">
                            <h3 class="campaign-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <div class="campaign-meta">
                                <div><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                            </div>
                            <p class="campaign-description"><?php echo htmlspecialchars($row['description']); ?></p>
                        </div>

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
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-campaigns">
                    <i class="fas fa-info-circle" style="font-size: 24px; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                    No pending campaigns at the moment.
                </div>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>

<?php
$conn->close();
?>