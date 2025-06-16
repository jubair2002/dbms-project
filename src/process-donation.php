<?php
require_once 'config.php';
session_start();

// Process the donation data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: campaign.php');
    exit;
}

// Get campaign ID and amount
if (!isset($_POST['campaign_id']) || empty($_POST['campaign_id'])) {
    header('Location: campaign.php');
    exit;
}

$campaign_id = intval($_POST['campaign_id']);
$amount = floatval($_POST['amount']);

// Validate amount
if ($amount <= 0) {
    header('Location: donate.php?campaign_id=' . $campaign_id . '&error=invalid_amount');
    exit;
}

// Get payment method
$payment_method = $_POST['payment_method'] ?? '';
if (!in_array($payment_method, ['credit_card', 'debit_card', 'mobile_banking', 'bank_transfer'])) {
    header('Location: donate.php?campaign_id=' . $campaign_id . '&error=invalid_payment');
    exit;
}

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']);
$user_id = 0;
$donor_name = 'Anonymous';

if ($user_logged_in) {
    $user_id = $_SESSION['user_id'];
    
    // Get donor name from database
    $user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_row = $user_result->fetch_assoc()) {
            $donor_name = $user_row['name'];
        }
        $user_stmt->close();
    } else {
        error_log("Failed to prepare user query: " . $conn->error);
    }
} else {
    // If not logged in, create a temporary user or use guest account
    // For demo purposes, we'll use a default guest user ID of 1
    $user_id = 1; // Guest user ID
    
    // You could also store the name and email for receipt purposes
    $donor_name = $_POST['name'] ?? 'Anonymous';
    $donor_email = $_POST['email'] ?? '';
}

// Get campaign title for notification
$campaign_stmt = $conn->prepare("SELECT title FROM campaigns WHERE id = ?");
if ($campaign_stmt) {
    $campaign_stmt->bind_param("i", $campaign_id);
    $campaign_stmt->execute();
    $campaign_result = $campaign_stmt->get_result();
    $campaign_title = 'Unknown Campaign';
    if ($campaign_row = $campaign_result->fetch_assoc()) {
        $campaign_title = $campaign_row['title'];
    }
    $campaign_stmt->close();
} else {
    error_log("Failed to prepare campaign query: " . $conn->error);
    $campaign_title = 'Unknown Campaign';
}

// In a real system, you would process the payment with a payment gateway here
// For this demo, we'll assume the payment was successful

// Start transaction for data consistency
$conn->begin_transaction();

try {
    // Insert donation record into the database
    $stmt = $conn->prepare("INSERT INTO donations (user_id, campaign_id, amount, donation_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $user_id, $campaign_id, $amount, $payment_method);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert donation record");
    }
    
    $donation_id = $conn->insert_id;
    $stmt->close();
    
    // Update campaign raised amount
    $update_stmt = $conn->prepare("UPDATE campaigns SET 
                                  raised = raised + ?, 
                                  donation_count = donation_count + 1 
                                  WHERE id = ?");
    $update_stmt->bind_param("di", $amount, $campaign_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update campaign");
    }
    $update_stmt->close();
    
    // Send notification to admin(s)
    // Get all admin users using user_type column
    $admin_stmt = $conn->prepare("SELECT id FROM users WHERE user_type = 'admin'");
    if (!$admin_stmt) {
        error_log("Admin query failed: " . $conn->error);
        throw new Exception("Failed to prepare admin query");
    }
    
    
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();
    
    // Prepare notification data
    $notification_title = "New Donation Received";
    $notification_message = sprintf("New donation received, amount is $%.2f", $amount);
    
    // Insert notification for each admin
    $notify_stmt = $conn->prepare("INSERT INTO notifications (recipient_id, sender_id, title, message, entity_type, entity_id) VALUES (?, NULL, ?, ?, 'donation', ?)");
    if (!$notify_stmt) {
        error_log("Failed to prepare notification query: " . $conn->error);
        throw new Exception("Failed to prepare notification query");
    }
    
    while ($admin_row = $admin_result->fetch_assoc()) {
        $admin_id = $admin_row['id'];
        $notify_stmt->bind_param("issi", $admin_id, $notification_title, $notification_message, $donation_id);
        
        if (!$notify_stmt->execute()) {
            error_log("Failed to send notification to admin ID: " . $admin_id . " - " . $conn->error);
            throw new Exception("Failed to send notification to admin ID: " . $admin_id);
        }
    }
    
    $notify_stmt->close();
    $admin_stmt->close();
    
    // Commit the transaction
    $conn->commit();
    
    // Redirect to thank you page
    header('Location: donation-success.php?campaign_id=' . $campaign_id . '&amount=' . $amount);
    exit;
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    
    // Log the error (in a real system, you'd use proper logging)
    error_log("Donation processing error: " . $e->getMessage());
    
    // Redirect with error
    header('Location: donate.php?campaign_id=' . $campaign_id . '&error=processing_error');
    exit;
}

$conn->close();
?>