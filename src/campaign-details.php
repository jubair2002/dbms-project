<?php
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: campaign.php');
    exit;
}

$campaign_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: campaign.php');
    exit;
}

$campaign = $result->fetch_assoc();

function calculatePercentage($raised, $goal)
{
    return min(100, round(($raised / $goal) * 100));
}
$percentage = calculatePercentage($campaign['raised'], $campaign['goal']);

$end_date = new DateTime($campaign['end_date']);
$today = new DateTime();
$days_remaining = $today <= $end_date ? $today->diff($end_date)->days : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0">
    <title><?php echo htmlspecialchars($campaign['name']); ?> - Campaign Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/campaign-details.css">
    <style>
        /* Additional inline styles to ensure full screen */
        html, body {
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
            overflow-x: hidden;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
        
        .container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-hands-helping"></i> Campaign Details</h1>
            <a href="campaign.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
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
                            <div class="progress-large" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>

                    <div class="donation-form">
                        <h3>Make a Donation</h3>
                        <form action="donate.php" method="post">
                            <div class="donation-amounts">
                                <button type="button" class="amount-btn">$25</button>
                                <button type="button" class="amount-btn">$50</button>
                                <button type="button" class="amount-btn">$100</button>
                                <button type="button" class="amount-btn">$250</button>
                                <button type="button" class="amount-btn">Custom</button>
                                <input type="hidden" name="amount" id="donationAmount" value="50">
                            </div>
                            <a href="donate.php?campaign_id=<?php echo $campaign['id']; ?>&amount=50" class="donate-btn">
                                <i class="fas fa-heart"></i> DONATE NOW
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="campaign-details-tabs">
                <div class="tabs-header">
                    <button class="tab-btn active" data-tab="story">Campaign Story</button>
                    <button class="tab-btn" data-tab="updates">Updates</button>
                    <button class="tab-btn" data-tab="volunteer">Volunteers</button>
                </div>

                <div class="tab-content">
                    <div class="tab-pane active" id="story">
                        <div class="campaign-description">
                            <h3>About This Campaign</h3>
                            <p><?php echo nl2br(htmlspecialchars($campaign['description'])); ?></p>

                            <h4>Our Goals</h4>
                            <ul>
                                <li>Provide immediate relief to those affected by the crisis</li>
                                <li>Establish sustainable support systems for long-term recovery</li>
                                <li>Build community resilience and preparedness for future challenges</li>
                                <li>Coordinate with local authorities and organizations for maximum impact</li>
                            </ul>

                            <h4>How Your Donation Helps</h4>
                            <p>Every dollar you contribute goes directly toward:</p>
                            <ul>
                                <li><strong>$25</strong> - Provides essential supplies for one family for a day</li>
                                <li><strong>$50</strong> - Covers emergency shelter for one person for a week</li>
                                <li><strong>$100</strong> - Supplies medical aid and first-aid kits for emergency response</li>
                                <li><strong>$250</strong> - Funds comprehensive support package for one family</li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-pane" id="updates">
                        <div class="campaign-updates">
                            <div class="update">
                                <div class="update-date">March 15, 2025</div>
                                <h4>Major Milestone Reached</h4>
                                <p>We're excited to announce that we've reached <?php echo $percentage; ?>% of our fundraising goal! Thanks to the incredible generosity of our donors, we've been able to provide immediate assistance to hundreds of families affected by this crisis.</p>
                            </div>
                            <div class="update">
                                <div class="update-date">March 10, 2025</div>
                                <h4>Distribution Center Established</h4>
                                <p>Our team has successfully established a distribution center in the affected area. We're now able to provide direct assistance and coordinate relief efforts more effectively.</p>
                            </div>
                            <div class="update">
                                <div class="update-date">March 5, 2025</div>
                                <h4>Campaign Launch</h4>
                                <p>We've officially launched this emergency relief campaign. Our initial assessment shows urgent need for shelter, food, medical supplies, and long-term support for affected communities.</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="volunteer">
                        <div class="volunteer-list">
                            <div class="volunteer">
                                <div class="volunteer-name">Sarah Johnson</div>
                                <div class="volunteer-role">Campaign Coordinator</div>
                            </div>
                            <div class="volunteer">
                                <div class="volunteer-name">Michael Chen</div>
                                <div class="volunteer-role">Logistics Manager</div>
                            </div>
                            <div class="volunteer">
                                <div class="volunteer-name">Emily Rodriguez</div>
                                <div class="volunteer-role">Community Outreach</div>
                            </div>
                            <div class="volunteer">
                                <div class="volunteer-name">David Thompson</div>
                                <div class="volunteer-role">Medical Coordinator</div>
                            </div>
                            <div class="volunteer">
                                <div class="volunteer-name">Lisa Park</div>
                                <div class="volunteer-role">Supply Distribution</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 8px;">
                            <h4>Want to Volunteer?</h4>
                            <p>We're always looking for dedicated volunteers to help with our relief efforts. Whether you can spare a few hours or want to take on a larger role, every contribution makes a difference.</p>
                            <a href="volunteer.php?campaign_id=<?php echo $campaign['id']; ?>" class="donate-btn" style="margin-top: 15px;">
                                <i class="fas fa-hands-helping"></i> Join Our Team
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass PHP variables to JavaScript -->
    <script>
        // Global variables from PHP
        const campaignData = {
            id: <?php echo $campaign['id']; ?>,
            name: '<?php echo addslashes($campaign['name']); ?>',
            raised: <?php echo $campaign['raised']; ?>,
            goal: <?php echo $campaign['goal']; ?>,
            percentage: <?php echo $percentage; ?>,
            donationCount: <?php echo $campaign['donation_count']; ?>
        };
    </script>
    
    <script src="assets/js/campaign-details.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>