<?php
// Include the database configuration file
require_once 'config.php';

// Query to fetch all campaigns
$sql = "SELECT * FROM campaigns ORDER BY created_at DESC";
$result = $conn->query($sql);

// Function to calculate percentage
function calculatePercentage($raised, $goal)
{
    return min(100, round(($raised / $goal) * 100));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Impact Initiatives</title>
    <link rel="stylesheet" href="assets/css/campaign.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>All Campaign</h1>
        </div>

        <div class="causes-grid">
            <?php
            // Check if there are campaigns in the database
            if ($result->num_rows > 0) {
                // Loop through each campaign
                while ($row = $result->fetch_assoc()) {
                    // Calculate progress percentage
                    $percentage = calculatePercentage($row['raised'], $row['goal']);
            ?>
                    <div class="cause-card">
                        <div class="cause-image">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width:100%; height:auto;">
                        </div>
                        <div class="cause-content">
                            <h3 class="cause-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="cause-description"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $row['progress_color']; ?>"></div>
                            </div>
                            <div class="cause-stats">
                                <div class="stats-left">
                                    <div class="goal">Goal: $<?php echo number_format($row['goal'], 0); ?></div>
                                    <div class="raised">Raised: $<?php echo number_format($row['raised'], 0); ?></div>
                                </div>
                                <div class="stats-right">
                                    <div class="donors"><?php echo $row['donation_count']; ?> donations</div>
                                </div>
                            </div>
                            <div class="spacer"></div> <!-- This div will push the button to the bottom -->
                            <a href="campaign-details.php?id=<?php echo $row['id']; ?>" class="view-details-btn">VIEW DETAILS</a>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p class='no-campaigns'>No campaigns found.</p>";
            }
            ?>
        </div>
    </div>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>