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

           <div id="donation-card" class="donation-card">
    <div class="card-header">
        <div class="logo-area">
            <i class="fas fa-heart"></i> Donation Certificate
        </div>
    </div>
    <div class="card-content">
        <div class="donation-icon">
            <i class="fas fa-hand-holding-heart"></i>
        </div>
        <h2 class="card-title">Thank You for Your Donation!</h2>
        <div class="card-amount">$<?php echo number_format($amount, 2); ?></div>
        <p class="card-campaign">to <?php echo htmlspecialchars($campaign['name']); ?></p>
        <p class="card-date">Date: <?php echo date('F j, Y'); ?></p>
        <div class="card-message">Your generosity makes a difference!</div>
    </div>
    <div class="card-footer">
        <p class="website">crisislink.org</p>
    </div>
</div>

<!-- Updated share-section with buttons to share the visual card -->
<div class="share-section">
    <h3 class="share-title">Spread the Word</h3>
    <div class="social-buttons">
        <!-- Facebook Share -->
        <a href="javascript:void(0);" onclick="shareOnFacebook()" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
        
        <!-- Twitter Share -->
        <a href="javascript:void(0);" onclick="shareOnTwitter()" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
        
        <!-- WhatsApp Share -->
        <a href="javascript:void(0);" onclick="shareOnWhatsApp()" class="social-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
        
        <!-- Email Share -->
        <a href="javascript:void(0);" onclick="shareViaEmail()" class="social-btn email"><i class="fas fa-envelope"></i></a>
        
        <!-- Download PDF -->
        <a href="javascript:void(0);" onclick="downloadPDF()" class="social-btn pdf-download"><i class="fas fa-file-pdf"></i></a>
    </div>
</div>

            <div class="action-buttons">
                <a href="campaign-details.php?id=<?php echo $campaign_id; ?>" class="btn primary-btn">Return to Campaign</a>
                <a href="campaign.php" class="btn secondary-btn">View All Campaigns</a>
            </div>
        </div>
    </div>

    <style>
    .donation-card {
        width: 500px;
        max-width: 100%;
        margin: 40px auto;
        background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%);
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #e0e0e0;
        display: none; /* Initially hidden, will be shown for sharing */
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .logo-area {
        font-size: 24px;
        font-weight: bold;
    }
    
    .card-content {
        padding: 30px;
        text-align: center;
    }
    
    .donation-icon {
        font-size: 50px;
        color: var(--primary-color);
        margin-bottom: 20px;
    }
    
    .card-title {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
    }
    
    .card-amount {
        font-size: 42px;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 5px;
    }
    
    .card-campaign {
        font-size: 18px;
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .card-date {
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .card-message {
        font-size: 16px;
        font-style: italic;
        color: #444;
        padding: 10px 0;
    }
    
    .card-footer {
        background-color: #f5f5f5;
        padding: 15px;
        text-align: center;
        border-top: 1px solid #e0e0e0;
    }
    
    .website {
        font-size: 14px;
        color: #666;
        margin: 0;
    }
    
    .pdf-download {
        background-color: #ff5722;
    }
    
    #share-canvas {
        display: none;
    }
    
    /* Make room for the new PDF button */
    .social-buttons {
        display: flex;
        justify-content: center;
        gap: 12px;
    }
</style>

<!-- Add HTML2Canvas and jsPDF libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Add this script at the bottom of your body tag -->
<script>
    // Get campaign and donation information
    const campaignName = "<?php echo htmlspecialchars($campaign['name']); ?>";
    const donationAmount = "<?php echo number_format($amount, 2); ?>";
    const currentUrl = window.location.href;
    
    // Create share message
    const shareTitle = "I just donated to " + campaignName + "!";
    const shareMessage = "I just donated $" + donationAmount + " to " + campaignName + ". Join me in making a difference!";
    
    // Function to create image from donation card
    async function createCardImage() {
        // Make the donation card visible
        const donationCard = document.getElementById('donation-card');
        donationCard.style.display = 'block';
        
        // Create the image
        const canvas = await html2canvas(donationCard, {
            scale: 2, // Higher quality
            backgroundColor: null,
            logging: false
        });
        
        // Hide the donation card again
        donationCard.style.display = 'none';
        
        return canvas.toDataURL('image/png');
    }
    
    // Facebook share function with image
    async function shareOnFacebook() {
        try {
            const imageUrl = await createCardImage();
            // Facebook doesn't allow direct image sharing via their API, we need to upload to a server first
            // For now, we'll share with the available methods
            const url = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(currentUrl) + 
                        "&quote=" + encodeURIComponent(shareMessage);
            window.open(url, "_blank", "width=600,height=400");
        } catch (err) {
            console.error("Error sharing to Facebook:", err);
            alert("There was an error preparing your share. Please try again.");
        }
    }
    
    // Twitter share function
    async function shareOnTwitter() {
        try {
            // Twitter doesn't support direct image uploads via url params either
            const url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(shareMessage) + 
                        "&url=" + encodeURIComponent(currentUrl);
            window.open(url, "_blank", "width=600,height=400");
        } catch (err) {
            console.error("Error sharing to Twitter:", err);
            alert("There was an error preparing your share. Please try again.");
        }
    }
    
    // WhatsApp share function
    async function shareOnWhatsApp() {
        try {
            // WhatsApp can only share text via web API
            const url = "https://api.whatsapp.com/send?text=" + encodeURIComponent(shareMessage + " " + currentUrl);
            window.open(url, "_blank");
        } catch (err) {
            console.error("Error sharing to WhatsApp:", err);
            alert("There was an error preparing your share. Please try again.");
        }
    }
    
    // Email share function with image attachment
    async function shareViaEmail() {
        try {
            const subject = encodeURIComponent(shareTitle);
            const body = encodeURIComponent(shareMessage + "\n\nYou can donate here: " + currentUrl);
            window.location.href = "mailto:?subject=" + subject + "&body=" + body;
        } catch (err) {
            console.error("Error sharing via email:", err);
            alert("There was an error preparing your email. Please try again.");
        }
    }
    
    // Download PDF function
    async function downloadPDF() {
        try {
            // Create image first
            const imageUrl = await createCardImage();
            
            // Create PDF using jsPDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            // Calculate aspect ratio to fit on PDF
            const imgProps = pdf.getImageProperties(imageUrl);
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            
            // Adjust image size for the PDF page
            const imgWidth = pageWidth - 40; // margins
            const imgHeight = (imgProps.height * imgWidth) / imgProps.width;
            
            // Add the image
            pdf.addImage(imageUrl, 'PNG', 20, 20, imgWidth, imgHeight);
            
            // Add some text
            pdf.setFontSize(10);
            pdf.text('Thank you for your donation! This certificate confirms your contribution.', 20, imgHeight + 30);
            
            // Download the PDF
            pdf.save('Donation_Certificate_' + campaignName.replace(/\s+/g, '_') + '.pdf');
            
        } catch (err) {
            console.error("Error generating PDF:", err);
            alert("There was an error generating your PDF. Please try again.");
        }
    }
</script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>