<?php
// Include database connection
require_once 'config.php';

// Calculate percentage for progress bars
function calculatePercentage($raised, $goal) {
  if ($goal <= 0) return 0;
  $percentage = ($raised / $goal) * 100;
  return min(round($percentage), 100);
}

// Fetch the latest 3 ongoing campaigns
$campaignsSql = "SELECT id, name, description, goal, raised, donation_count, progress_color 
                FROM campaigns 
                WHERE status = 'approved' AND progress = 'ongoing' 
                ORDER BY start_date DESC 
                LIMIT 3";
$campaignsResult = $conn->query($campaignsSql);

// Initialize variables with default values
$totalFunds = 0;
$totalDonors = 0;
$activeCampaigns = 0;
$chartLabels = [];
$raisedData = [];
$goalData = [];

// Calculate total funds raised from all campaigns
$totalFundsSql = "SELECT SUM(raised) as total_raised FROM campaigns WHERE status = 'approved'";
if ($result = $conn->query($totalFundsSql)) {
    $row = $result->fetch_assoc();
    $totalFunds = $row['total_raised'] ?? 0;
    $result->free();
} else {
    error_log("Total funds query failed: " . $conn->error);
}

// Count total donors
$totalDonorsSql = "SELECT SUM(donation_count) as total_donors FROM campaigns WHERE status = 'approved'";
if ($result = $conn->query($totalDonorsSql)) {
    $row = $result->fetch_assoc();
    $totalDonors = $row['total_donors'] ?? 0;
    $result->free();
} else {
    error_log("Total donors query failed: " . $conn->error);
}

// Count active campaigns
$activeCampaignsSql = "SELECT COUNT(*) as active_campaigns FROM campaigns WHERE status = 'approved' AND progress = 'ongoing'";
if ($result = $conn->query($activeCampaignsSql)) {
    $row = $result->fetch_assoc();
    $activeCampaigns = $row['active_campaigns'] ?? 0;
    $result->free();
} else {
    error_log("Active campaigns query failed: " . $conn->error);
}

// Fetch campaign data for trend line chart (last 6 campaigns)
$campaignTrendSql = "SELECT 
                    name,
                    goal, 
                    raised
                    FROM campaigns 
                    WHERE status = 'approved'
                    ORDER BY start_date DESC 
                    LIMIT 6";
                    
if ($result = $conn->query($campaignTrendSql)) {
    while ($row = $result->fetch_assoc()) {
        $chartLabels[] = $row['name'];
        $raisedData[] = $row['raised'];
        $goalData[] = $row['goal'];
    }
    // Reverse arrays to show oldest to newest (left to right)
    $chartLabels = array_reverse($chartLabels);
    $raisedData = array_reverse($raisedData);
    $goalData = array_reverse($goalData);
    $result->free();
} else {
    error_log("Campaign trend query failed: " . $conn->error);
    // Ensure we have at least empty arrays
    $chartLabels = $chartLabels ?: ['Campaign 1', 'Campaign 2', 'Campaign 3', 'Campaign 4', 'Campaign 5', 'Campaign 6'];
    $raisedData = $raisedData ?: [0, 0, 0, 0, 0, 0];
    $goalData = $goalData ?: [0, 0, 0, 0, 0, 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Disaster Management Dashboard</title>
  <link rel="stylesheet" href="assets/css/dashboardSummary.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <div class="dashboard-container">
    <!-- Existing Emergency Alerts Card -->
    <div class="card card-emergency">
      <div class="card-header">
        <h3><i class="fas fa-exclamation-triangle"></i> Active Emergencies</h3>
      </div>
      <ul class="card-list">
        <li><span class="status-indicator status-critical"></span>Flood in Kurigram - 5,000 affected</li>
        <li><span class="status-indicator status-warning"></span>Fire in Dhaka - 2 blocks affected</li>
      </ul>
    </div>

    <!-- Ongoing Campaigns Card -->
    <div class="card ongoing-campaigns">
      <div class="card-header">
        <h3><i class="fas fa-project-diagram"></i> Ongoing Campaigns</h3>
      </div>
      <div class="campaign-container">
        <?php
        if ($campaignsResult->num_rows > 0) {
          while($campaign = $campaignsResult->fetch_assoc()) {
            $percentage = calculatePercentage($campaign['raised'], $campaign['goal']);
        ?>
            <div class="campaign-item">
              <h4><?php echo htmlspecialchars($campaign['name']); ?></h4>
      
              <div class="campaign-stats">
                <span><i class="fas fa-users"></i> <?php echo $campaign['donation_count']; ?> Donations</span>
                <span><i class="fas fa-dollar-sign"></i> $<?php echo number_format($campaign['raised'], 2); ?> Raised</span>
              </div>
              <div class="campaign-progress">
                <div class="progress-container">
                  <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background: <?php echo $campaign['progress_color']; ?>;"></div>
                </div>
                <p class="sub-text"><?php echo $percentage; ?>% of $<?php echo number_format($campaign['goal'], 2); ?> Goal</p>
              </div>
            </div>
        <?php
          }
        } else {
        ?>
            <div class="campaign-item empty-campaigns">
              <i class="fas fa-info-circle"></i>
              <p>No ongoing campaigns at the moment.</p>
            </div>
        <?php
        }
        ?>
      </div>
    </div>
    
<!-- Summary Stats Card -->
<div class="card card-stats">
  <div class="card-header">
    <h3><i class="fas fa-chart-line"></i> Quick Stats</h3>
  </div>
  <div class="stats-container">
    <div class="stat-item">
      <div class="stat-value">$<?php echo number_format($totalFunds, 2); ?></div>
      <div class="stat-label">Total Funds Raised</div>
    </div>
    <div class="stat-item">
      <div class="stat-value"><?php echo number_format($totalDonors); ?></div>
      <div class="stat-label">Total Donors</div>
    </div>
    <div class="stat-item">
      <div class="stat-value"><?php echo number_format($activeCampaigns); ?></div>
      <div class="stat-label">Active Campaigns</div>
    </div>
  </div>
</div>

<!-- Donation Chart Card -->
<div class="card card-chart">
  <div class="card-header">
    <h3><i class="fas fa-chart-line"></i> Campaign Performance</h3>
  </div>
  <div class="chart-container">
    <canvas id="campaignChart"></canvas>
  </div>
</div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('campaignChart').getContext('2d');
  const campaignChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($chartLabels); ?>,
      datasets: [
        {
          label: 'Raised Amount',
          data: <?php echo json_encode($raisedData); ?>,
          backgroundColor: 'rgba(213, 0, 0, 0.1)',
          borderColor: '#d50000',
          borderWidth: 3,
          pointBackgroundColor: '#d50000',
          pointBorderColor: '#fff',
          pointRadius: 5,
          pointHoverRadius: 7,
          fill: true,
          tension: 0.4
        },
        {
          label: 'Goal Amount',
          data: <?php echo json_encode($goalData); ?>,
          backgroundColor: 'rgba(0, 0, 0, 0.05)',
          borderColor: '#000',
          borderWidth: 2,
          pointBackgroundColor: '#000',
          pointBorderColor: '#fff',
          pointRadius: 4,
          pointHoverRadius: 6,
          borderDash: [5, 5],
          fill: false,
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0,0,0,0.1)'
          },
          ticks: {
            color: '#000',
            callback: function(value) {
              return '$' + value.toLocaleString();
            }
          },
          title: {
            display: true,
            text: 'Amount ($)',
            color: '#000',
            font: {
              weight: 'bold'
            }
          }
        },
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#000'
          },
          title: {
            display: true,
            text: 'Campaigns',
            color: '#000',
            font: {
              weight: 'bold'
            }
          }
        }
      },
      plugins: {
        legend: {
          labels: {
            color: '#000',
            font: {
              weight: 'bold'
            }
          },
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.dataset.label + ': $' + context.raw.toLocaleString();
            }
          }
        }
      },
      interaction: {
        mode: 'index',
        intersect: false
      }
    }
  });
});
</script>
</body>
</html>