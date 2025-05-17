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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($campaign['name']); ?> - Campaign Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/campaign-details.css">
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
                                <input type="hidden" name="amount" id="donationAmount">
                            </div>
                            <a href="donate.php?campaign_id=<?php echo $campaign['id']; ?>" class="donate-btn">
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
                    <button class="tab-btn" data-tab="donors">Donors</button>
                    <button class="tab-btn" data-tab="volunteer">Volunteers</button>
                </div>

                <div class="tab-content">
                    <div class="tab-pane active" id="story">
                        <div class="campaign-description">
                            <h3>About This Campaign</h3>
                            <p><?php echo htmlspecialchars($campaign['description']); ?></p>

                            <h4>Our Goals</h4>
                            <ul>
                                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                                <li>Vivamus lacinia odio vitae vestibulum vestibulum.</li>
                                <li>Cras porttitor metus non dolor vehicula, id dapibus ex facilisis.</li>
                                <li>Proin scelerisque diam nec consequat tempor.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-pane" id="updates">
                        <div class="campaign-updates">
                            <div class="update">
                                <div class="update-date">March 15, 2025</div>
                                <h4>Major Milestone Reached</h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
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
                        </div>
                    </div>

                    <div class="tab-pane" id="volunteer">
                        <div class="volunteer-list">
                            <div class="volunteer">
                                <div class="volunteer-name">John Doe</div>
                                <div class="volunteer-role">Coordinator</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/campaign-details.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>