<?php
require_once 'config.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_role = $_SESSION['user_type'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #ff0000;
            --light-color: #ffffff;
            --gray-color: #e0e0e0;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: var(--primary-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .dashboard-title h1 {
            font-size: 28px;
            color: var(--primary-color);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .dashboard-card {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 220px;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            font-size: 36px;
            margin-bottom: 15px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-icon i {
            color: var(--accent-color); /* Icons remain red for accent */
        }

        .dashboard-card h2 {
            font-size: 18px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .dashboard-card p {
            color: var(--secondary-color);
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 14px;
            justify-content: center;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            width: 100%;
            color: var(--light-color); /* White text for all buttons */
            font-weight: 500;

            /* Unified button style */
            background-color: var(--primary-color); /* Black button color */
        }

        .btn:hover {
            background-color: var(--secondary-color); /* Darker gray on hover for all buttons */
        }

        /* Remove specific button styles, as they are now unified */
        /*
        .btn-primary { background-color: var(--accent-color); }
        .btn-primary:hover { background-color: #cc0000; }
        .btn-info { background-color: #17a2b8; }
        .btn-info:hover { background-color: #138496; }
        .btn-success { background-color: #28a745; }
        .btn-success:hover { background-color: #218838; }
        .btn-secondary { background-color: var(--secondary-color); }
        .btn-secondary:hover { background-color: var(--primary-color); }
        .btn-warning { background-color: #ffc107; }
        .btn-warning:hover { background-color: #e0a800; }
        .btn-purple { background-color: #6f42c1; }
        .btn-purple:hover { background-color: #5a32a3; }
        */

        /* Disabled card for non-admin users */
        .card-disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        .card-disabled .dashboard-card {
            background-color: #f8f8f8;
        }

        .card-disabled .btn {
            background-color: #999;
            cursor: not-allowed;
            color: #ccc; /* Lighter text for disabled button */
        }

        /* Loading Animation */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--accent-color); /* Accent color for spinner */
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .dashboard-card {
                min-height: 200px;
                padding: 15px;
            }

            .card-icon {
                font-size: 30px;
                height: 40px;
            }

            .dashboard-card h2 {
                font-size: 16px;
            }

            .dashboard-card p {
                font-size: 12px;
            }

            .btn {
                font-size: 12px;
                padding: 6px 12px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .dashboard-title h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="loading" id="loadingScreen">
        <div class="loading-spinner"></div>
    </div>

    <div class="container">
        <div class="dashboard-title">
            <h1><i class="fas fa-tachometer-alt"></i> Campaign Dashboard</h1>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h2>Create Campaign</h2>
                <p>Start a new campaign and make a difference</p>
                <a href="createCampaign.php" class="btn" onclick="showLoading()">
                    <i class="fas fa-rocket"></i> Create
                </a>
            </div>

            <div class="<?php echo ($user_role != 'admin') ? 'card-disabled' : ''; ?>">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h2>Campaign Requests</h2>
                    <p>Review and approve pending requests</p>
                    <?php if($user_role == 'admin'): ?>
                        <a href="campaignRequest.php" class="btn" onclick="showLoading()">
                            <i class="fas fa-tasks"></i> Manage
                        </a>
                    <?php else: ?>
                        <span class="btn">Admin Only</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="<?php echo ($user_role != 'admin') ? 'card-disabled' : ''; ?>">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h2>Track Progress</h2>
                    <p>Monitor volunteer tasks in real-time</p>
                    <?php if($user_role == 'admin'): ?>
                        <a href="track_task_progress.php" class="btn" onclick="showLoading()">
                            <i class="fas fa-analytics"></i> View
                        </a>
                    <?php else: ?>
                        <span class="btn">Admin Only</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <h2>All Campaigns</h2>
                <p>Browse and explore active campaigns</p>
                <a href="campaign.php" class="btn" onclick="showLoading()">
                    <i class="fas fa-eye"></i> View All
                </a>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h2>Assign Volunteers</h2>
                <p>Manage and assign volunteers to campaigns</p>
                <a href="assign_volunteer.php" class="btn" onclick="showLoading()">
                    <i class="fas fa-user-plus"></i> Assign
                </a>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h2>Relief Allocation</h2>
                <p>Manage and distribute relief resources</p>
                <a href="relief.php" class="btn" onclick="showLoading()">
                    <i class="fas fa-box-open"></i> Allocate
                </a>
            </div>
        </div>
    </div>

    <script>
        // Function to show loading screen
        function showLoading() {
            document.getElementById('loadingScreen').style.display = 'flex';
        }

        // Add hover effect
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                if (!this.parentElement.classList.contains('card-disabled')) {
                    this.style.transform = 'translateY(-5px)';
                }
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>