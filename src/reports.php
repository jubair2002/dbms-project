<?php
require_once 'config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if we're generating a report
if (isset($_POST['campaign_id']) && $_POST['campaign_id'] != '') {
    generateCampaignReport($conn, intval($_POST['campaign_id']));
    exit();
}

// Function to generate PDF report
function generateCampaignReport($conn, $campaign_id)
{
    // Include TCPDF library
    require_once('tcpdf/tcpdf.php');

    // Get campaign details
    $sql = "SELECT * FROM campaigns WHERE id = $campaign_id";
    $result = $conn->query($sql);
    $campaign = $result->fetch_assoc();

    if (!$campaign) {
        die("Campaign not found");
    }

    // Get volunteers assigned to this campaign
    $sql = "SELECT u.fname, u.lname, u.email, a.task_name, a.status 
            FROM assignments a
            JOIN users u ON a.volunteer_id = u.id
            WHERE a.campaign_id = $campaign_id";
    $volunteers = $conn->query($sql);

    // Get donations for this campaign
    $sql = "SELECT d.amount, d.donation_date, d.donation_type, u.fname, u.lname
            FROM donations d
            JOIN users u ON d.user_id = u.id
            WHERE d.campaign_id = $campaign_id
            ORDER BY d.donation_date DESC";
    $donations = $conn->query($sql);

    // Calculate total donations
    $total_donations = 0;
    if ($donations->num_rows > 0) {
        while ($don = $donations->fetch_assoc()) {
            $total_donations += $don['amount'];
        }
        $donations->data_seek(0); // Reset pointer to beginning
    }

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('CrisisLink');
    $pdf->SetAuthor('CrisisLink');
    $pdf->SetTitle('Campaign Report: ' . $campaign['name']);
    $pdf->SetSubject('Campaign Report');

    // Set default header data with logo
    $pdf->SetHeaderData('', 0, 'CrisisLink', 'Campaign Progress Report', array(0, 0, 0), array(0, 0, 0));
    $pdf->setHeaderFont(array('helvetica', '', 10));
    $pdf->setFooterFont(array('helvetica', '', 8));

    // Set margins
    $pdf->SetMargins(15, 25, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Add a page
    $pdf->AddPage();

    // Set font for title
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 10, 'Campaign Report: ' . $campaign['name'], 0, 1, 'C');
    $pdf->Ln(10);

    // Campaign summary box
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'CAMPAIGN SUMMARY', 0, 1, 'L', true);
    $pdf->SetFont('helvetica', '', 10);

    // Create summary table
    $summary = '<table cellspacing="0" cellpadding="5" border="0">
        <tr>
            <td width="30%"><strong>Description:</strong></td>
            <td width="70%">' . $campaign['description'] . '</td>
        </tr>
        <tr>
            <td><strong>Goal Amount:</strong></td>
            <td>$' . number_format($campaign['goal'], 2) . '</td>
        </tr>
        <tr>
            <td><strong>Amount Raised:</strong></td>
            <td>$' . number_format($campaign['raised'], 2) . '</td>
        </tr>
        <tr>
            <td><strong>Progress:</strong></td>
            <td>' . number_format(($campaign['raised'] / $campaign['goal']) * 100, 2) . '%</td>
        </tr>
        <tr>
            <td><strong>Start Date:</strong></td>
            <td>' . date('F j, Y', strtotime($campaign['start_date'])) . '</td>
        </tr>
        <tr>
            <td><strong>End Date:</strong></td>
            <td>' . date('F j, Y', strtotime($campaign['end_date'])) . '</td>
        </tr>
    </table>';

    $pdf->writeHTML($summary, true, false, true, false, '');
    $pdf->Ln(10);

    // Progress bar visualization
    $progress = ($campaign['raised'] / $campaign['goal']) * 100;
    $progressBar = '<div style="background-color:#f1f1f1; width:100%; height:20px; border-radius:10px;">
        <div style="background-color:#4CAF50; width:' . $progress . '%; height:20px; border-radius:10px;"></div>
    </div>';
    $pdf->writeHTML($progressBar, true, false, true, false, '');
    $pdf->Ln(15);

    // Volunteers section
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'VOLUNTEERS ASSIGNED', 0, 1);
    $pdf->Ln(5);

    if ($volunteers->num_rows > 0) {
        // Create volunteers table
        $volunteerTable = '<table border="1" cellpadding="4">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th width="25%">Name</th>
                    <th width="25%">Email</th>
                    <th width="25%">Task</th>
                    <th width="25%">Status</th>
                </tr>
            </thead>
            <tbody>';

        while ($vol = $volunteers->fetch_assoc()) {
            $volunteerTable .= '<tr>
                <td>' . $vol['fname'] . ' ' . $vol['lname'] . '</td>
                <td>' . $vol['email'] . '</td>
                <td>' . $vol['task_name'] . '</td>
                <td>' . ucfirst($vol['status']) . '</td>
            </tr>';
        }

        $volunteerTable .= '</tbody></table>';
        $pdf->writeHTML($volunteerTable, true, false, true, false, '');
    } else {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 8, 'No volunteers assigned to this campaign', 0, 1);
    }
    $pdf->Ln(15);

    // Donations section
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'DONATION SUMMARY', 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 8, 'Total Donations Received: $' . number_format($total_donations, 2), 0, 1);
    $pdf->Ln(5);

    if ($donations->num_rows > 0) {
        // Create donations table
        $donationTable = '<table border="1" cellpadding="4">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th width="20%">Date</th>
                    <th width="25%">Donor</th>
                    <th width="20%">Amount</th>
                    <th width="20%">Payment Method</th>
                </tr>
            </thead>
            <tbody>';

        while ($don = $donations->fetch_assoc()) {
            $donationTable .= '<tr>
                <td>' . date('M j, Y', strtotime($don['donation_date'])) . '</td>
                <td>' . $don['fname'] . ' ' . $don['lname'] . '</td>
                <td>$' . number_format($don['amount'], 2) . '</td>
                <td>' . ucwords(str_replace('_', ' ', $don['donation_type'])) . '</td>
            </tr>';
        }

        $donationTable .= '</tbody></table>';
        $pdf->writeHTML($donationTable, true, false, true, false, '');
    } else {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 8, 'No donations received for this campaign', 0, 1);
    }
    $pdf->Ln(15);

    // Add summary statistics
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'CAMPAIGN STATISTICS', 0, 1);
    $pdf->Ln(5);

    $stats = '<table cellspacing="0" cellpadding="5">
        <tr>
            <td width="50%"><strong>Total Volunteers:</strong></td>
            <td width="50%">' . $volunteers->num_rows . '</td>
        </tr>
        <tr>
            <td><strong>Total Donations:</strong></td>
            <td>' . $donations->num_rows . '</td>
        </tr>
        <tr>
            <td><strong>Average Donation:</strong></td>
            <td>$' . ($donations->num_rows > 0 ? number_format($total_donations / $donations->num_rows, 2) : '0.00') . '</td>
        </tr>
        <tr>
            <td><strong>Days Remaining:</strong></td>
            <td>' . max(0, floor((strtotime($campaign['end_date']) - time()) / (60 * 60 * 24))) . ' days</td>
        </tr>
    </table>';
    $pdf->writeHTML($stats, true, false, true, false, '');
    $pdf->Ln(10);

    // Add a final note
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->MultiCell(0, 8, 'Report generated on ' . date('F j, Y \a\t g:i a') . ' by CrisisLink. For any questions, please contact support@crisislink.org.', 0, 'C');

    // Output PDF
    $pdf->Output('campaign_report_' . $campaign['name'] . '.pdf', 'D');
}

// Get all campaigns from database
$sql = "SELECT id, name, description, goal, raised FROM campaigns ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Reports - CrisisLink</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a1a1a;
            --secondary-color: #f8f9fa;
            --accent-color:rgb(38, 50, 41);
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --surface-white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-primary);
            line-height: 1.6;
            font-size: 14px;
        }

        .container {
            margin: 0 auto;
            padding: 2rem;
        }

        .campaigns-section {
            background: var(--surface-white);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .section-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(to right, #fafafa, #ffffff);
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .campaigns-table {
            width: 100%;
            border-collapse: collapse;
        }

        .campaigns-table thead {
            background-color: #fafafa;
        }

        .campaigns-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .campaigns-table td {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }

        .campaigns-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .campaigns-table tbody tr:hover {
            background-color: #fafbfc;
        }

        /* Campaign Details */
        .campaign-info {
            min-width: 320px;
        }

        .campaign-name {
            font-weight: 600;
            font-size: 15px;
            color: var(--text-primary);
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .campaign-description {
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.5;
        }

        .campaign-meta {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .meta-badge {
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 500;
        }

        /* Financial Progress */
        .financial-info {
            min-width: 280px;
        }

        .financial-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .financial-label {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .financial-value {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .goal-amount {
            color: var(--text-secondary);
        }

        .raised-amount {
            color: var(--success-color);
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            height: 6px;
            background-color: #f3f4f6;
            border-radius: 3px;
            overflow: hidden;
            margin: 12px 0;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--success-color), #34d399);
            border-radius: 3px;
            transition: width 0.6s ease;
            position: relative;
        }

        .progress-percentage {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-primary);
            text-align: center;
        }

        /* Action Button */
        .action-cell {
            text-align: center;
            min-width: 140px;
        }

        .generate-btn {
            background: linear-gradient(135deg, var(--accent-color),rgb(5, 5, 5));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-transform: none;
            letter-spacing: 0;
        }

        .generate-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            filter: brightness(1.05);
        }

        .generate-btn:active {
            transform: translateY(0);
        }

        /* Empty State */
        .no-campaigns {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--text-secondary);
            font-size: 24px;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .empty-description {
            font-size: 14px;
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 1.5rem;
            }

            .campaigns-table th,
            .campaigns-table td {
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .campaigns-table {
                font-size: 13px;
            }

            .campaigns-table th,
            .campaigns-table td {
                padding: 0.75rem;
            }

            .campaign-info {
                min-width: auto;
            }

            .financial-info {
                min-width: auto;
            }
        }

        /* Loading State */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Utility Classes */
        .text-success {
            color: var(--success-color);
        }

        .text-warning {
            color: var(--warning-color);
        }

        .bg-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .bg-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .bg-primary {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php
        // Calculate overview stats
        $total_campaigns = $result->num_rows;
        $total_goal = 0;
        $total_raised = 0;
        $active_campaigns = 0;

        if ($result->num_rows > 0) {
            $campaigns_data = [];
            while ($row = $result->fetch_assoc()) {
                $campaigns_data[] = $row;
                $total_goal += $row['goal'];
                $total_raised += $row['raised'];
                $active_campaigns++;
            }
            $result->data_seek(0); // Reset pointer
        ?>

            <!-- Campaigns Table -->
            <div class="campaigns-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-list"></i>
                        Campaign Overview
                    </h2>
                </div>

                <table class="campaigns-table">
                    <thead>
                        <tr>
                            <th>Campaign Details</th>
                            <th>Financial Progress</th>
                            <th>Performance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns_data as $campaign) {
                            $progress = ($campaign['raised'] / $campaign['goal']) * 100;
                            $status_class = $progress >= 100 ? 'text-success' : ($progress >= 50 ? 'text-warning' : '');
                        ?>
                            <tr>
                                <td class="campaign-info">
                                    <div class="campaign-name"><?php echo htmlspecialchars($campaign['name']); ?></div>
                                    <div class="campaign-description">
                                        <?php echo htmlspecialchars(substr($campaign['description'], 0, 120)) . '...'; ?>
                                    </div>
                                </td>

                                <td class="financial-info">
                                    <div class="financial-row">
                                        <span class="financial-label">Goal</span>
                                        <span class="financial-value goal-amount">$<?php echo number_format($campaign['goal'], 0); ?></span>
                                    </div>
                                    <div class="financial-row">
                                        <span class="financial-label">Raised</span>
                                        <span class="financial-value raised-amount">$<?php echo number_format($campaign['raised'], 0); ?></span>
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?php echo min(100, $progress); ?>%"></div>
                                    </div>
                                </td>

                                <td>
                                    <div class="progress-percentage <?php echo $status_class; ?>">
                                        <?php echo number_format($progress, 1); ?>%
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                                        <?php
                                        if ($progress >= 100) {
                                            echo '<span class="text-success"><i class="fas fa-check-circle"></i> Goal Achieved</span>';
                                        } elseif ($progress >= 75) {
                                            echo '<span class="text-warning"><i class="fas fa-exclamation-circle"></i> Near Goal</span>';
                                        } else {
                                            echo '<span><i class="fas fa-clock"></i> In Progress</span>';
                                        }
                                        ?>
                                    </div>
                                </td>

                                <td class="action-cell">
                                    <form method="post" style="margin: 0;">
                                        <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
                                        <button type="submit" class="generate-btn">
                                            <i class="fas fa-download"></i>
                                            Generate Report
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php
        } else {
        ?>

            <!-- Empty State -->
            <div class="campaigns-section">
                <div class="no-campaigns">
                    <div class="empty-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="empty-title">No Campaigns Available</div>
                    <div class="empty-description">
                        Create your first campaign to start generating comprehensive reports and tracking progress.
                    </div>
                </div>
            </div>

        <?php
        }
        ?>
    </div>

    <script>
        // Add loading state to buttons when clicked
        document.querySelectorAll('.generate-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                this.disabled = true;

                // Add loading class to the parent form
                const form = this.closest('form');
                if (form) {
                    form.classList.add('loading');
                }

                // Automatically submit the form
                form.submit();

                // Re-enable button after 5 seconds in case submission fails
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-download"></i> Generate Report';
                    this.disabled = false;
                    if (form) {
                        form.classList.remove('loading');
                    }
                }, 3000);
            });
        });

        // Add confirmation for successful PDF generation
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('report_generated')) {
            alert('Report generated successfully! The download should start automatically.');
        }
    </script>
</body>

</html>

<?php
// Close connection
$conn->close();
?>