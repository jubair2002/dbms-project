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

// Get relief allocations for this campaign
$allocations_stmt = $conn->prepare("
    SELECT rc.category_name, SUM(ca.allocated_amount) as total_amount
    FROM campaign_allocations ca
    JOIN relief_categories rc ON ca.category_id = rc.id
    WHERE ca.campaign_id = ?
    GROUP BY rc.category_name
    ORDER BY total_amount DESC
");
$allocations_stmt->bind_param("i", $campaign_id);
$allocations_stmt->execute();
$allocations_result = $allocations_stmt->get_result();
$category_allocations = [];

while ($row = $allocations_result->fetch_assoc()) {
    $category_allocations[] = $row;
}

// Calculate remaining funds and allocation percentage
$remaining_funds = $campaign['raised'] - $campaign['allocated'];
$allocation_percentage = $campaign['raised'] > 0 ? min(100, round(($campaign['allocated'] / $campaign['raised']) * 100)) : 0;

// Get volunteers assigned to this campaign
$volunteers_stmt = $conn->prepare("
    SELECT a.id, a.task_name, a.priority, a.status, a.deadline, 
           u.id as user_id, u.fname, u.lname, u.email, u.picture,
           COUNT(t.id) as task_count
    FROM assignments a
    JOIN users u ON a.volunteer_id = u.id
    LEFT JOIN tasks t ON a.id = t.assignment_id
    WHERE a.campaign_id = ?
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$volunteers_stmt->bind_param("i", $campaign_id);
$volunteers_stmt->execute();
$volunteers_result = $volunteers_stmt->get_result();
$assigned_volunteers = [];

while ($row = $volunteers_result->fetch_assoc()) {
    $assigned_volunteers[] = $row;
}

// Get total counts for volunteer stats
$volunteer_counts = [
    'total' => count($assigned_volunteers),
    'completed' => 0,
    'in_progress' => 0,
    'not_started' => 0
];

foreach ($assigned_volunteers as $volunteer) {
    if ($volunteer['status'] === 'completed') {
        $volunteer_counts['completed']++;
    } elseif ($volunteer['status'] === 'in-progress') {
        $volunteer_counts['in_progress']++;
    } else {
        $volunteer_counts['not_started']++;
    }
}
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
        /* Additional styles for volunteer section */
        .volunteer-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .volunteer-stat {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 1px solid #eee;
        }

        .volunteer-stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .volunteer-stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .volunteer-priority {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }

        .priority-high {
            background-color: #f44336;
        }

        .priority-medium {
            background-color: #ff9800;
        }

        .priority-low {
            background-color: #4CAF50;
        }

        .volunteer-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }

        .status-completed {
            background-color: #4CAF50;
        }

        .status-in-progress {
            background-color: #2196F3;
        }

        .status-assigned {
            background-color: #9E9E9E;
        }

        .status-rejected {
            background-color: #f44336;
        }

        .status-not-started {
            background-color: #FF9800;
        }

        .volunteer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        .volunteer-info {
            display: flex;
            align-items: center;
        }

        .volunteer-name {
            font-weight: 600;
        }

        .volunteer-email {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }

        .volunteer-deadline {
            font-size: 14px;
            color: #666;
        }

        .task-count {
            background-color: #e0e0e0;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 12px;
            color: #333;
        }

        @media (max-width: 768px) {
            .volunteer-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .volunteer {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }

            .volunteer-info {
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            .volunteer-stats {
                grid-template-columns: 1fr;
            }
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
                            <div class="stat-value">$<?php echo number_format($campaign['allocated'], 0); ?></div>
                            <div class="stat-label">Allocated</div>
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

                    <!-- Compact Relief Allocations Section -->
                    <div class="compact-allocations">
                        <div class="allocation-title">
                            Relief Fund Allocations
                            <span><?php echo $allocation_percentage; ?>% of raised funds allocated</span>
                        </div>

                        <div class="compact-stats">
                            <div class="compact-stat">
                                <div class="compact-stat-value">$<?php echo number_format($campaign['allocated'], 0); ?></div>
                                <div class="compact-stat-label">Total Allocated</div>
                            </div>
                            <div class="compact-stat">
                                <div class="compact-stat-value">$<?php echo number_format($remaining_funds, 0); ?></div>
                                <div class="compact-stat-label">Funds Available</div>
                            </div>
                            <div class="compact-stat">
                                <div class="compact-stat-value"><?php echo count($category_allocations); ?></div>
                                <div class="compact-stat-label">Categories</div>
                            </div>
                        </div>

                        <div class="allocation-progress">
                            <div class="allocation-progress-fill" style="width: <?php echo $allocation_percentage; ?>%"></div>
                        </div>

                        <?php if (count($category_allocations) > 0): ?>
                            <?php foreach ($category_allocations as $allocation):
                                $percentage = ($allocation['total_amount'] / $campaign['allocated']) * 100;
                            ?>
                                <div class="allocation-category">
                                    <div class="allocation-category-header">
                                        <div><?php echo htmlspecialchars($allocation['category_name']); ?></div>
                                        <div>$<?php echo number_format($allocation['total_amount'], 0); ?></div>
                                    </div>
                                    <div class="allocation-category-bar">
                                        <div class="allocation-category-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-allocations-message">
                                No allocations have been made yet.
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_role'] === 'admin')): ?>
                            <div class="admin-action">
                                <a href="relief.php?campaign_id=<?php echo $campaign_id; ?>" class="admin-btn">
                                    <i class="fas fa-money-bill-wave"></i> Manage Allocations
                                </a>
                            </div>
                        <?php endif; ?>
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
                        <!-- Volunteer Stats Summary -->
                        <div class="volunteer-stats">
                            <div class="volunteer-stat">
                                <div class="volunteer-stat-value"><?php echo $volunteer_counts['total']; ?></div>
                                <div class="volunteer-stat-label">Total Volunteers</div>
                            </div>
                            <div class="volunteer-stat">
                                <div class="volunteer-stat-value"><?php echo $volunteer_counts['completed']; ?></div>
                                <div class="volunteer-stat-label">Completed</div>
                            </div>
                            <div class="volunteer-stat">
                                <div class="volunteer-stat-value"><?php echo $volunteer_counts['in_progress']; ?></div>
                                <div class="volunteer-stat-label">In Progress</div>
                            </div>
                            <div class="volunteer-stat">
                                <div class="volunteer-stat-value"><?php echo $volunteer_counts['not_started']; ?></div>
                                <div class="volunteer-stat-label">Not Started</div>
                            </div>
                        </div>

                        <!-- Volunteer List -->
                        <div class="volunteer-list">
                            <?php if (count($assigned_volunteers) > 0): ?>
                                <?php foreach ($assigned_volunteers as $volunteer): ?>
                                    <div class="volunteer">
                                        <div class="volunteer-info">
                                            <img src="<?php echo !empty($volunteer['picture']) ? htmlspecialchars($volunteer['picture']) : 'assets/images/default-avatar.png'; ?>"
                                                alt="<?php echo htmlspecialchars($volunteer['fname'] . ' ' . $volunteer['lname']); ?>"
                                                class="volunteer-avatar">
                                            <div>
                                                <div class="volunteer-name"><?php echo htmlspecialchars($volunteer['fname'] . ' ' . $volunteer['lname']); ?></div>
                                                <div class="volunteer-email"><?php echo htmlspecialchars($volunteer['email']); ?></div>
                                            </div>
                                        </div>
                                        <div class="volunteer-task">
                                            <?php echo htmlspecialchars($volunteer['task_name']); ?>
                                            <?php if ($volunteer['task_count'] > 0): ?>
                                                <span class="task-count"><?php echo $volunteer['task_count']; ?> subtasks</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="volunteer-deadline">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?php echo date('M j, Y', strtotime($volunteer['deadline'])); ?>
                                        </div>
                                        <div>
                                            <span class="volunteer-priority priority-<?php echo strtolower($volunteer['priority']); ?>">
                                                <?php echo ucfirst($volunteer['priority']); ?>
                                            </span>
                                            <span class="volunteer-status status-<?php echo str_replace(' ', '-', strtolower($volunteer['status'])); ?>">
                                                <?php echo ucfirst(str_replace('-', ' ', $volunteer['status'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="text-align: center; padding: 30px; color: #666;">
                                    <i class="fas fa-user-friends" style="font-size: 48px; color: #ddd; margin-bottom: 15px; display: block;"></i>
                                    <h4>No Volunteers Assigned Yet</h4>
                                    <p>There are currently no volunteers assigned to this campaign.</p>
                                </div>
                            <?php endif; ?>
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
            allocated: <?php echo $campaign['allocated']; ?>,
            percentage: <?php echo $percentage; ?>,
            allocationPercentage: <?php echo $allocation_percentage; ?>,
            donationCount: <?php echo $campaign['donation_count']; ?>
        };
    </script>

    <script src="assets/js/campaign-details.js"></script>
</body>

</html>

<?php
$stmt->close();
$allocations_stmt->close();
$volunteers_stmt->close();
$conn->close();
?>