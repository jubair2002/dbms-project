<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
require_once 'config.php';

// Initialize variables
$message = "";
$error = "";
$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Handle allocation deletion (admin only)
if (isset($_POST['delete_allocation']) && $user_type === 'admin') {
    $allocation_id = intval($_POST['allocation_id']);
    $delete_campaign_id = intval($_POST['delete_campaign_id']);
    
    // Get allocation amount to restore to campaign
    $get_amount_sql = "SELECT allocated_amount FROM campaign_allocations WHERE id = ?";
    $stmt = $conn->prepare($get_amount_sql);
    $stmt->bind_param("i", $allocation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $amount_to_restore = $row['allocated_amount'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete allocation
            $delete_sql = "DELETE FROM campaign_allocations WHERE id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("i", $allocation_id);
            $stmt->execute();
            
            // Update campaign allocated amount
            $update_campaign_sql = "UPDATE campaigns SET allocated = allocated - ? WHERE id = ?";
            $stmt = $conn->prepare($update_campaign_sql);
            $stmt->bind_param("di", $amount_to_restore, $delete_campaign_id);
            $stmt->execute();
            
            $conn->commit();
            $message = "Allocation deleted successfully and funds restored to campaign.";
            $campaign_id = $delete_campaign_id;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error deleting allocation: " . $e->getMessage();
        }
    }
}

// Handle new allocation submission
if (isset($_POST['allocate']) && $campaign_id > 0) {
    $category_id = intval($_POST['category_id']);
    $amount = floatval($_POST['amount']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Validate amount
    if ($amount <= 0) {
        $error = "Amount must be greater than 0.";
    } else {
        // Check remaining funds
        $check_sql = "SELECT name, raised, allocated FROM campaigns WHERE id = ? AND status = 'approved'";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($campaign = $result->fetch_assoc()) {
            $remaining = $campaign['raised'] - $campaign['allocated'];
            
            if ($amount > $remaining) {
                $error = "Allocation amount ($" . number_format($amount, 2) . ") exceeds remaining funds ($" . number_format($remaining, 2) . ").";
            } else {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Insert allocation
                    $insert_sql = "INSERT INTO campaign_allocations (campaign_id, category_id, allocated_amount, notes, allocated_by) 
                                   VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("iidsi", $campaign_id, $category_id, $amount, $notes, $user_id);
                    $stmt->execute();
                    
                    // Update campaign allocated amount
                    $update_sql = "UPDATE campaigns SET allocated = allocated + ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("di", $amount, $campaign_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    $message = "Successfully allocated $" . number_format($amount, 2) . " to the selected category.";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Error processing allocation: " . $e->getMessage();
                }
            }
        } else {
            $error = "Campaign not found or not approved.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relief Resource Allocation</title>
    <link rel="stylesheet" href="assets/css/relief.css">
</head>
<body>
    <div class="container">
        <div>
        <h1>Relief Resource Allocation</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($campaign_id == 0): ?>
            <!-- Campaign Selection Form -->
            <h2>Select a Campaign</h2>
            <form method="get" action="">
                <label for="campaign_id">Choose an approved campaign:</label>
                <select name="campaign_id" id="campaign_id" required>
                    <option value="">-- Select Campaign --</option>
                    <?php
                    $campaigns_sql = "SELECT id, name, raised, allocated FROM campaigns WHERE status = 'approved' ORDER BY name";
                    $result = $conn->query($campaigns_sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $remaining = $row['raised'] - $row['allocated'];
                            echo "<option value='" . $row['id'] . "'>" . 
                                 htmlspecialchars($row['name']) . 
                                 " (Remaining: $" . number_format($remaining, 2) . ")</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit">Load Campaign</button>
            </form>
            
        <?php else: ?>
            <!-- Campaign Details and Allocation Form -->
            <a href="relief.php" class="back-link">Back to Campaign Selection</a>
            
            <?php
            // Fetch campaign details
            $campaign_sql = "SELECT id, name, raised, allocated FROM campaigns WHERE id = ? AND status = 'approved'";
            $stmt = $conn->prepare($campaign_sql);
            $stmt->bind_param("i", $campaign_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($campaign = $result->fetch_assoc()):
                $remaining = $campaign['raised'] - $campaign['allocated'];
            ?>
                <div class="campaign-info">
                    <h3><?php echo htmlspecialchars($campaign['name']); ?></h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Total Raised</div>
                            <div class="info-value">$<?php echo number_format($campaign['raised'], 2); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Allocated</div>
                            <div class="info-value">$<?php echo number_format($campaign['allocated'], 2); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Remaining Funds</div>
                            <div class="info-value <?php echo $remaining > 0 ? 'positive' : 'negative'; ?>">
                                $<?php echo number_format($remaining, 2); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($remaining > 0): ?>
                    <h2>Allocate Resources</h2>
                    <form method="post" action="">
                        <label for="category_id">Relief Category:</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">-- Select Category --</option>
                            <?php
                            $categories_sql = "SELECT id, category_name, description FROM relief_categories WHERE status = 'active' ORDER BY category_name";
                            $cat_result = $conn->query($categories_sql);
                            
                            if ($cat_result && $cat_result->num_rows > 0) {
                                while ($cat = $cat_result->fetch_assoc()) {
                                    echo "<option value='" . $cat['id'] . "'>" . 
                                         htmlspecialchars($cat['category_name']) . 
                                         " - " . htmlspecialchars($cat['description']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        
                        <label for="amount">Amount to Allocate ($):</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" max="<?php echo $remaining; ?>" required>
                        
                        <label for="notes">Notes (Optional):</label>
                        <textarea name="notes" id="notes" placeholder="Enter any additional notes about this allocation..."></textarea>
                        
                        <button type="submit" name="allocate">Allocate Funds</button>
                    </form>
                <?php else: ?>
                    <div class="message error no-funds-message">No remaining funds available for allocation.</div>
                <?php endif; ?>
                
                <h2>Allocation History</h2>
                <?php
                // Fetch allocation history
                $history_sql = "SELECT ca.id, ca.allocated_amount, ca.notes, ca.date_allocated,
                                rc.category_name, 
                                CONCAT(u.fname, ' ', u.lname) as allocated_by_name
                                FROM campaign_allocations ca
                                JOIN relief_categories rc ON ca.category_id = rc.id
                                JOIN users u ON ca.allocated_by = u.id
                                WHERE ca.campaign_id = ?
                                ORDER BY ca.date_allocated DESC";
                $stmt = $conn->prepare($history_sql);
                $stmt->bind_param("i", $campaign_id);
                $stmt->execute();
                $history_result = $stmt->get_result();
                
                if ($history_result && $history_result->num_rows > 0):
                ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Allocated By</th>
                                <th>Notes</th>
                                <?php if ($user_type === 'admin'): ?>
                                    <th>Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($allocation = $history_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i', strtotime($allocation['date_allocated'])); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['category_name']); ?></td>
                                    <td>$<?php echo number_format($allocation['allocated_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['allocated_by_name']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['notes'] ?: '-'); ?></td>
                                    <?php if ($user_type === 'admin'): ?>
                                        <td>
                                            <form method="post" action="" class="delete-form" 
                                                  onsubmit="return confirm('Are you sure you want to delete this allocation?');">
                                                <input type="hidden" name="allocation_id" value="<?php echo $allocation['id']; ?>">
                                                <input type="hidden" name="delete_campaign_id" value="<?php echo $campaign_id; ?>">
                                                <button type="submit" name="delete_allocation" class="delete">Delete</button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No allocations have been made for this campaign yet.</p>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="message error">Campaign not found or not approved.</div>
                <a href="relief.php" class="back-link">Back to Campaign Selection</a>
            <?php endif; ?>
            
        <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>