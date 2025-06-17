<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Metrics</title>
    <style>
        :root {
            --primary: #000000;
            --success: #2ecc71;
            --info: #3498db;
            --warning: #f39c12;
            --danger: #e74c3c;
            --secondary: #7f8c8d;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fc;
        }

        .main-content {
            padding: 20px;
        }

        /* Cards Section */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0.15rem 0.35rem rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            border-left: 4px solid var(--success);
        }

        .card.primary {
            border-left-color: var(--primary);
        }

        .card.success {
            border-left-color: var(--success);
        }

        .card.info {
            border-left-color: var(--info);
        }

        .card.warning {
            border-left-color: var(--warning);
        }

        .card-title {
            color: var(--secondary);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .card-change {
            display: flex;
            align-items: center;
            font-size: 12px;
        }

        .card-change.positive {
            color: var(--success);
        }

        .card-change.negative {
            color: var(--danger);
        }

        /* Charts and Tables Section */
        .data-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .chart-container,
        .donations-container {
            background-color: white;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0.15rem 0.35rem rgba(0, 0, 0, 0.1);
            border: 1px solid #e3e6f0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e3e6f0;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }

        .time-period {
            display: flex;
            gap: 10px;
        }

        .time-period button {
            background: none;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .time-period button:hover {
            background-color: var(--light);
        }

        .time-period button.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .chart-placeholder {
            height: 300px;
            background-color: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
            border-radius: 5px;
            border: 1px dashed #ddd;
        }

        .donations-list {
            list-style: none;
        }

        .donation-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e3e6f0;
        }

        .donation-item:last-child {
            border-bottom: none;
        }

        .donation-info {
            display: flex;
            align-items: center;
        }

        .donation-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            /* Fallback background */
        }

        .donation-avatar .profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .donation-details h5 {
            font-size: 14px;
            margin-bottom: 5px;
            color: var(--dark);
        }



        .donation-details p {
            font-size: 12px;
            color: var(--secondary);
        }

        .donation-amount {
            font-weight: 700;
            color: var(--success);
        }

        .data-container {
            display: grid;
            grid-template-columns: 1.8fr 1.2fr;
            gap: 20px;
            align-items: stretch;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="main-content">
        <?php
        require_once 'config.php';

        //  Total Volunteers
        $result_volunteers = mysqli_query($conn, "SELECT COUNT(*) AS total_volunteers FROM users WHERE user_type = 'volunteer'");
        $row_volunteers = mysqli_fetch_assoc($result_volunteers);
        $totalVolunteers = $row_volunteers['total_volunteers'];

        //  Ongoing Campaigns
        $result_campaigns = mysqli_query($conn, "SELECT COUNT(*) AS ongoing_campaigns FROM campaigns WHERE progress = 'ongoing'");
        $row_campaigns = mysqli_fetch_assoc($result_campaigns);
        $ongoingCampaigns = $row_campaigns['ongoing_campaigns'];

        //  Tasks Running
        $result_tasks = mysqli_query($conn, "SELECT COUNT(*) AS tasks_running FROM assignments WHERE status IN ('assigned', 'in-progress', 'not-started')");
        $row_tasks = mysqli_fetch_assoc($result_tasks);
        $tasksRunning = $row_tasks['tasks_running'];

        //  Total Donation Amount
        $result_donations = mysqli_query($conn, "SELECT SUM(amount) AS total_donation_amount FROM donations");
        $row_donations = mysqli_fetch_assoc($result_donations);
        $totalDonationAmount = number_format($row_donations['total_donation_amount'], 0); // Format as currency
        ?>

        <div class="cards-container">
            <div class="card primary">
                <div class="card-title">Total Volunteers</div>
                <div class="card-value"><?php echo $totalVolunteers; ?></div>
            </div>

            <div class="card success">
                <div class="card-title">Ongoing Campaigns</div>
                <div class="card-value"><?php echo $ongoingCampaigns; ?></div>

            </div>

            <div class="card info">
                <div class="card-title">Tasks Running</div>
                <div class="card-value"><?php echo $tasksRunning; ?></div>
            </div>

            <div class="card warning">
                <div class="card-title">Donation Amount</div>
                <div class="card-value">$<?php echo $totalDonationAmount; ?></div>
            </div>
        </div>


        <div class="data-container">
            <!-- Chart Container (Left Side) -->
            <div class="chart-container">
                <div class="section-header">
                    <div class="section-title">Donation Analytics</div>
                    <div class="time-period">
                        <button class="period-btn active" data-period="daily">Daily</button>
                        <button class="period-btn" data-period="weekly">Weekly</button>
                        <button class="period-btn" data-period="monthly">Monthly</button>
                        <button class="period-btn" data-period="yearly">Yearly</button>
                    </div>
                </div>
                <div class="chart-placeholder">
                    <canvas id="donationChart"></canvas>
                </div>
            </div>

            <div class="donations-container">
                <div class="section-header">
                    <div class="section-title">Latest Donations</div>
                    <a href="#" style="font-size: 12px; color: var(--primary);">View All</a>
                </div>
                <ul class="donations-list">
                    <?php
                    require_once 'config.php';

                    $query = "SELECT u.fname, u.lname, u.picture, d.amount, d.donation_date 
                  FROM donations d
                  JOIN users u ON d.user_id = u.id
                  ORDER BY d.donation_date DESC
                  LIMIT 5";

                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $name = htmlspecialchars($row['fname'] . ' ' . $row['lname']);
                            $amount = '$' . number_format($row['amount'], 2);
                            $profile_pic = $row['picture']; // Directly use the path from database

                            // Format date
                            $date = new DateTime($row['donation_date']);
                            $now = new DateTime();
                            $diff = $now->diff($date);

                            if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
                                $date_str = "Today, " . $date->format('g:i A');
                            } elseif ($diff->days == 1) {
                                $date_str = "Yesterday, " . $date->format('g:i A');
                            } else {
                                $date_str = $date->format('M j, g:i A');
                            }
                    ?>
                            <li class="donation-item">
                                <div class="donation-info">
                                    <div class="donation-avatar">
                                        <?php if (!empty($profile_pic)): ?>
                                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="<?php echo $name; ?>" class="profile-pic">
                                        <?php else: ?>
                                            <!-- Fallback to initials if no picture -->
                                            <?php echo strtoupper(substr($row['fname'], 0, 1) . substr($row['lname'], 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="donation-details">
                                        <h5><?php echo $name; ?></h5>
                                        <p><?php echo $date_str; ?></p>
                                    </div>
                                </div>
                                <div class="donation-amount"><?php echo $amount; ?></div>
                            </li>
                    <?php
                        }
                    } else {
                        echo '<li class="donation-item">No donations found</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>

        <!-- Add this CSS to your style section -->
        <style>
            .data-container {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 20px;
                align-items: stretch;
                /* Make both containers same height */
            }

            .chart-container,
            .donations-container {
                height: 100%;
                /* Fill the available height */
            }

            .chart-placeholder {
                height: calc(100% - 60px);
                /* Account for header height */
            }

            .donations-list {
                max-height: calc(100% - 60px);
                overflow-y: hidden;
            }
        </style>

        <?php
        // Include your config file that contains the database connection
        require_once 'config.php';

        // Function to fetch chart data
        function getChartData($conn, $period)
        {
            switch ($period) {
                case 'daily':
                    $query = "SELECT DATE(donation_date) as label, SUM(amount) as total 
                      FROM donations 
                      WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      GROUP BY label ORDER BY label";
                    break;
                case 'weekly':
                    $query = "SELECT DATE_FORMAT(donation_date, '%Y-%u') as label, 
                      SUM(amount) as total 
                      FROM donations 
                      WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
                      GROUP BY label ORDER BY label";
                    break;
                case 'monthly':
                    $query = "SELECT DATE_FORMAT(donation_date, '%Y-%m') as label, 
                      SUM(amount) as total 
                      FROM donations 
                      WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                      GROUP BY label ORDER BY label";
                    break;
                case 'yearly':
                    $query = "SELECT YEAR(donation_date) as label, SUM(amount) as total 
                      FROM donations 
                      GROUP BY label ORDER BY label";
                    break;
            }

            $result = mysqli_query($conn, $query);
            $data = ['labels' => [], 'data' => []];

            while ($row = mysqli_fetch_assoc($result)) {
                $data['labels'][] = $row['label'];
                $data['data'][] = $row['total'];
            }

            return $data;
        }

        // Get all chart data
        $dailyData = getChartData($conn, 'daily');
        $weeklyData = getChartData($conn, 'weekly');
        $monthlyData = getChartData($conn, 'monthly');
        $yearlyData = getChartData($conn, 'yearly');
        ?>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Initialize chart
            let donationChart;
            const ctx = document.getElementById('donationChart').getContext('2d');

            // Prepare chart data from PHP
            const chartData = {
                daily: <?php echo json_encode($dailyData); ?>,
                weekly: <?php echo json_encode($weeklyData); ?>,
                monthly: <?php echo json_encode($monthlyData); ?>,
                yearly: <?php echo json_encode($yearlyData); ?>
            };

            // Format dates based on period
            function formatLabels(labels, period) {
                return labels.map(label => {
                    const date = new Date(label);
                    switch (period) {
                        case 'daily':
                            return date.toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric'
                            });
                        case 'weekly':
                            return 'Week ' + Math.ceil(date.getDate() / 7) + ' ' + date.toLocaleDateString('en-US', {
                                month: 'short'
                            });
                        case 'monthly':
                            return date.toLocaleDateString('en-US', {
                                month: 'short',
                                year: 'numeric'
                            });
                        case 'yearly':
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric'
                            });
                    }
                });
            }

            // Initialize chart
            document.addEventListener('DOMContentLoaded', function() {
                donationChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: formatLabels(chartData.daily.labels, 'daily'),
                        datasets: [{
                            label: 'Donation Amount',
                            data: chartData.daily.data,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // Handle period button clicks
                document.querySelectorAll('.period-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const period = this.dataset.period;
                        donationChart.data.labels = formatLabels(chartData[period].labels, period);
                        donationChart.data.datasets[0].data = chartData[period].data;
                        donationChart.update();

                        // Update active button
                        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            });
        </script>
    </div>
    </div>
</body>

</html>