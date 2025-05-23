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
    <link rel="stylesheet" href="assets/css/campaignRequest.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-list"></i> Campaign Requests</h1>
            <a href="campaignSummary.php" class="back-btn">
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