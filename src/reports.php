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
function generateCampaignReport($conn, $campaign_id) {
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
        while($don = $donations->fetch_assoc()) {
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
    $pdf->SetHeaderData('', 0, 'CrisisLink', 'Campaign Progress Report', array(0,0,0), array(0,0,0));
    $pdf->setHeaderFont(Array('helvetica', '', 10));
    $pdf->setFooterFont(Array('helvetica', '', 8));

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
            <td>' . number_format(($campaign['raised']/$campaign['goal'])*100, 2) . '%</td>
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
    $progress = ($campaign['raised']/$campaign['goal'])*100;
    $progressBar = '<div style="background-color:#f1f1f1; width:100%; height:20px; border-radius:10px;">
        <div style="background-color:#4CAF50; width:'.$progress.'%; height:20px; border-radius:10px;"></div>
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
        
        while($vol = $volunteers->fetch_assoc()) {
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
        
        while($don = $donations->fetch_assoc()) {
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
            <td>$' . ($donations->num_rows > 0 ? number_format($total_donations/$donations->num_rows, 2) : '0.00') . '</td>
        </tr>
        <tr>
            <td><strong>Days Remaining:</strong></td>
            <td>' . max(0, floor((strtotime($campaign['end_date']) - time())/(60*60*24))) . ' days</td>
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
    <title>Campaign Reports</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #000000;
            line-height: 1.6;
        }
        
        .header {
            background-color: #000000;
            color: #ffffff;
            padding: 15px 0;
            text-align: center;
            border-bottom: 3px solid #dc3545;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 400;
            letter-spacing: 1px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .campaigns-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        
        .campaigns-table th {
            background-color: #dc3545;
            color: #ffffff;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }
        
        .campaigns-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        
        .campaigns-table tr:hover {
            background-color: #fff5f5;
        }
        
        .campaign-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: #000000;
        }
        
        .campaign-description {
            color: #666666;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .progress-container {
            width: 100%;
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin: 8px 0;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
            transition: width 0.3s ease;
        }
        
        .stats-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px 0;
            font-size: 0.9rem;
        }
        
        .goal-amount {
            font-weight: 600;
            color: #000000;
        }
        
        .raised-amount {
            color: #333333;
        }
        
        .progress-percentage {
            font-weight: 600;
            color: #dc3545;
        }
        
        .generate-btn {
            background-color: #dc3545;
            color: #ffffff;
            padding: 12px 24px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: 2px solid #dc3545;
        }
        
        .generate-btn:hover {
            background-color: #ffffff;
            color: #dc3545;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
        }
        
        .no-campaigns {
            text-align: center;
            padding: 60px 20px;
            color: #666666;
            font-size: 1.2rem;
            background-color: #f8f8f8;
            border: 1px solid #e0e0e0;
        }
        
        .campaign-info {
            min-width: 300px;
        }
        
        .financial-info {
            min-width: 200px;
        }
        
        .action-cell {
            text-align: center;
            min-width: 150px;
        }
        
        @media (max-width: 768px) {
            .campaigns-table {
                font-size: 0.85rem;
            }
            
            .campaigns-table th,
            .campaigns-table td {
                padding: 15px 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .container {
                padding: 20px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CAMPAIGN REPORTS</h1>
    </div>
    
    <div class="container">
        <?php 
        if ($result->num_rows > 0) {
        ?>
        <table class="campaigns-table">
            <thead>
                <tr>
                    <th>Campaign Details</th>
                    <th>Financial Progress</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($campaign = $result->fetch_assoc()) {
                    $progress = ($campaign['raised'] / $campaign['goal']) * 100;
                ?>
                <tr>
                    <td class="campaign-info">
                        <div class="campaign-name"><?php echo htmlspecialchars($campaign['name']); ?></div>
                        <div class="campaign-description"><?php echo htmlspecialchars(substr($campaign['description'], 0, 120)); ?>...</div>
                    </td>
                    
                    <td class="financial-info">
                        <div class="stats-row">
                            <span class="goal-amount">Goal: $<?php echo number_format($campaign['goal'], 2); ?></span>
                        </div>
                        <div class="stats-row">
                            <span class="raised-amount">Raised: $<?php echo number_format($campaign['raised'], 2); ?></span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo min(100, $progress); ?>%"></div>
                        </div>
                        <div class="stats-row">
                            <span class="progress-percentage"><?php echo number_format($progress, 1); ?>% Complete</span>
                        </div>
                    </td>
                    
                    <td class="action-cell">
                        <form method="post" style="margin: 0;">
                            <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
                            <button type="submit" class="generate-btn">Generate Report</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
        } else {
            echo '<div class="no-campaigns">No campaigns found. Create a campaign to generate reports.</div>';
        }
        ?>
    </div>
</body>
</html>

<?php
// Close connection
$conn->close();
?>