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

if ($user_logged_in) {
    $user_id = $_SESSION['user_id'];
} else {
    // If not logged in, create a temporary user or use guest account
    // For demo purposes, we'll use a default guest user ID of 1
    // In a real system, you'd either create a new user or implement a guest donation system
    $user_id = 1; // Guest user ID
    
    // You could also store the name and email for receipt purposes
    $donor_name = $_POST['name'] ?? 'Anonymous';
    $donor_email = $_POST['email'] ?? '';
}

// In a real system, you would process the payment with a payment gateway here
// For this demo, we'll assume the payment was successful

// Insert donation record into the database
$stmt = $conn->prepare("INSERT INTO donations (user_id, campaign_id, amount, donation_type) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iids", $user_id, $campaign_id, $amount, $payment_method);

if ($stmt->execute()) {
    // Update campaign raised amount
    $update_stmt = $conn->prepare("UPDATE campaigns SET 
                                  raised = raised + ?, 
                                  donation_count = donation_count + 1 
                                  WHERE id = ?");
    $update_stmt->bind_param("di", $amount, $campaign_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Redirect to thank you page
    header('Location: donation-success.php?campaign_id=' . $campaign_id . '&amount=' . $amount);
    exit;
} else {
    // Handle error
    header('Location: donate.php?campaign_id=' . $campaign_id . '&error=db_error');
    exit;
}

$stmt->close();
$conn->close();
?>