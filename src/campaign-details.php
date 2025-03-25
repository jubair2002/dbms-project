<?php
// Include the database configuration file
require_once 'config.php';

// Check if campaign ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to campaigns page if no ID is provided
    header('Location: campaign.php');
    exit;
}

// Get the campaign ID and sanitize it
$campaign_id = intval($_GET['id']);

// Prepare and execute the query to fetch campaign details
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if campaign exists
if ($result->num_rows === 0) {
    // Redirect to campaigns page if campaign doesn't exist
    header('Location: campaign.php');
    exit;
}

// Fetch the campaign data
$campaign = $result->fetch_assoc();

// Calculate progress percentage
function calculatePercentage($raised, $goal) {
    return min(100, round(($raised / $goal) * 100));
}
$percentage = calculatePercentage($campaign['raised'], $campaign['goal']);

// Calculate days remaining
$end_date = new DateTime($campaign['end_date']);
$today = new DateTime();
$days_remaining = $today <= $end_date ? $today->diff($end_date)->days : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($campaign['name']); ?> - Campaign Details</title>
    <link rel="stylesheet" href="assets/css/campaign-details.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Campaign Details</h1>
            <a href="campaign.php" class="back-btn">BACK TO CAMPAIGNS</a>
        </div>
        
        <div class="campaign-details">
            <div class="campaign-header">
                <h2><?php echo htmlspecialchars($campaign['name']); ?></h2>
                <span class="campaign-category"><?php echo htmlspecialchars($campaign['category']); ?></span>
            </div>
            
            <div class="campaign-content">
                <div class="campaign-main-image">
                    <img src="<?php echo htmlspecialchars($campaign['image_url']); ?>" alt="<?php echo htmlspecialchars($campaign['name']); ?>">
                </div>
                
                <div class="campaign-info">
                    <div class="campaign-stats-cards">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $percentage; ?>%</div>
                            <div class="stat-label">Funded</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">$<?php echo number_format($campaign['raised'], 0); ?></div>
                            <div class="stat-label">Raised</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">$<?php echo number_format($campaign['goal'], 0); ?></div>
                            <div class="stat-label">Goal</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $campaign['donation_count']; ?></div>
                            <div class="stat-label">Donors</div>
                        </div>
                        
                    </div>
                    
                    <div class="campaign-progress">
                        <div class="progress-bar-large">
                            <div class="progress-large" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $campaign['progress_color']; ?>"></div>
                        </div>
                    </div>
                    
                    <div class="donation-form">
                        <h3>Make a Donation</h3>
                        <div class="donation-amounts">
                            <button class="amount-btn">$25</button>
                            <button class="amount-btn">$50</button>
                            <button class="amount-btn">$100</button>
                            <button class="amount-btn">$250</button>
                            <button class="amount-btn">Custom</button>
                        </div>
                        <button class="donate-btn">DONATE NOW</button>
                    </div>
                </div>
            </div>
            
            <div class="campaign-details-tabs">
                <div class="tabs-header">
                    <button class="tab-btn active" data-tab="story">Campaign Story</button>
                    <button class="tab-btn" data-tab="updates">Updates</button>
                    <button class="tab-btn" data-tab="donors">Donors</button>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="story">
                        <div class="campaign-description">
                            <h3>About This Campaign</h3>
                            <p><?php echo htmlspecialchars($campaign['description']); ?></p>
                            
                            <!-- Extended description - in a real app, this would come from the database -->
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum vestibulum. Cras porttitor metus non dolor vehicula, id dapibus ex facilisis. Proin scelerisque diam nec consequat tempor. Nullam eu nulla ut elit efficitur facilisis. Sed nec nibh vel orci rutrum interdum. Nam non felis sapien.</p>
                            
                            <p>Etiam eget hendrerit elit. Ut luctus, est vel tincidunt porttitor, mi tellus molestie arcu, et faucibus arcu elit id velit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras vel dictum elit. Etiam luctus nunc ut erat sagittis, vel molestie ante elementum.</p>
                            
                            <h4>Our Goals</h4>
                            <ul>
                                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                                <li>Vivamus lacinia odio vitae vestibulum vestibulum.</li>
                                <li>Cras porttitor metus non dolor vehicula, id dapibus ex facilisis.</li>
                                <li>Proin scelerisque diam nec consequat tempor.</li>
                            </ul>
                            
                            <h4>How Your Donation Helps</h4>
                            <p>Nullam eu nulla ut elit efficitur facilisis. Sed nec nibh vel orci rutrum interdum. Nam non felis sapien. Etiam eget hendrerit elit. Ut luctus, est vel tincidunt porttitor, mi tellus molestie arcu, et faucibus arcu elit id velit.</p>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="updates">
                        <div class="campaign-updates">
                            <div class="update">
                                <div class="update-date">March 15, 2025</div>
                                <h4>Major Milestone Reached</h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum vestibulum. Cras porttitor metus non dolor vehicula, id dapibus ex facilisis.</p>
                            </div>
                            
                            <div class="update">
                                <div class="update-date">February 28, 2025</div>
                                <h4>Campaign Launched</h4>
                                <p>Etiam eget hendrerit elit. Ut luctus, est vel tincidunt porttitor, mi tellus molestie arcu, et faucibus arcu elit id velit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="donors">
                        <div class="donor-list">
                            <div class="donor">
                                <div class="donor-name">Anonymous</div>
                                <div class="donor-amount">$500</div>
                                <div class="donor-date">March 20, 2025</div>
                            </div>
                            
                            <div class="donor">
                                <div class="donor-name">John Doe</div>
                                <div class="donor-amount">$250</div>
                                <div class="donor-date">March 18, 2025</div>
                            </div>
                            
                            <div class="donor">
                                <div class="donor-name">Jane Smith</div>
                                <div class="donor-amount">$100</div>
                                <div class="donor-date">March 15, 2025</div>
                            </div>
                            
                            <div class="donor">
                                <div class="donor-name">Robert Johnson</div>
                                <div class="donor-amount">$75</div>
                                <div class="donor-date">March 10, 2025</div>
                            </div>
                            
                            <div class="donor">
                                <div class="donor-name">Lisa Williams</div>
                                <div class="donor-amount">$50</div>
                                <div class="donor-date">March 5, 2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and panes
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Donation amount buttons
            const amountButtons = document.querySelectorAll('.amount-btn');
            amountButtons.forEach(button => {
                button.addEventListener('click', function() {
                    amountButtons.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });
        });
    </script>
    
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>