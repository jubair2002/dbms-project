<?php
require_once 'config.php';

if (!isset($_GET['campaign_id']) || empty($_GET['campaign_id'])) {
    header('Location: campaign.php');
    exit;
}

$campaign_id = intval($_GET['campaign_id']);
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

// Fetch campaign details
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: campaign.php');
    exit;
}

$campaign = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Successful - <?php echo htmlspecialchars($campaign['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #D10000;
            /* Red */
            --secondary-color: #990000;
            /* Darker red */
            --dark-color: #222;
            /* Dark gray/black */
            --light-color: #fff;
            /* White */
            --border-color: #e0e0e0;
        }

        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .success-container {
            background-color: var(--light-color);
            border-radius: 8px;
            box-shadow: 0 3px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .success-icon {
            font-size: 80px;
            color: var(--primary-color);
            margin-bottom: 20px;
            animation: bounce 1s;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-20px);
            }

            60% {
                transform: translateY(-10px);
            }
        }

        .success-title {
            font-size: 32px;
            margin-bottom: 15px;
            color: var(--dark-color);
            font-weight: 600;
        }

        .donation-details {
            background-color: rgba(209, 0, 0, 0.05);
            border-radius: 8px;
            padding: 25px;
            margin: 30px auto;
            display: inline-block;
            border: 1px solid rgba(209, 0, 0, 0.1);
            max-width: 80%;
        }

        .donation-amount {
            font-size: 36px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .campaign-name {
            font-size: 18px;
            margin-top: 0;
            color: var(--dark-color);
        }

        .action-buttons {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .primary-btn {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }

        .primary-btn:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .secondary-btn {
            background-color: var(--light-color);
            color: var(--dark-color);
            border: 1px solid var(--dark-color);
        }

        .secondary-btn:hover {
            background-color: var(--dark-color);
            color: var(--light-color);
        }

        .receipt-info {
            margin-top: 30px;
            font-size: 15px;
            color: #666;
            line-height: 1.6;
        }

        .impact-message {
            margin: 30px 0;
            font-size: 18px;
            line-height: 1.6;
            color: var(--dark-color);
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .share-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
        }

        .share-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--dark-color);
        }

        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            font-size: 18px;
        }

        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .facebook {
            background-color: #3b5998;
        }

        .twitter {
            background-color: #1da1f2;
        }

        .whatsapp {
            background-color: #25D366;
        }

        .email {
            background-color: #D44638;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .donation-details,
            .impact-message {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 class="success-title">Thank You for Your Donation!</h1>
            <p>Your generous contribution has been successfully processed.</p>

            <div class="donation-details">
                <div class="donation-amount">$<?php echo number_format($amount, 2); ?></div>
                <p class="campaign-name">to <?php echo htmlspecialchars($campaign['name']); ?></p>
            </div>

            <div class="impact-message">
                Your donation will help make a real difference. Together, we can achieve our goals and create positive change.
            </div>

            <div class="receipt-info">
                <p>A receipt has been sent to your email address.</p>
                <p>Transaction ID: <?php echo strtoupper(substr(md5(time()), 0, 10)); ?></p>
                <p>Date: <?php echo date('F j, Y'); ?></p>
            </div>

            <div class="share-section">
                <h3 class="share-title">Spread the Word</h3>
                <div class="social-buttons">
                    <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="social-btn email"><i class="fas fa-envelope"></i></a>
                </div>
            </div>

            <div class="action-buttons">
                <a href="campaign-details.php?id=<?php echo $campaign_id; ?>" class="btn primary-btn">Return to Campaign</a>
                <a href="campaign.php" class="btn secondary-btn">View All Campaigns</a>
            </div>
        </div>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>