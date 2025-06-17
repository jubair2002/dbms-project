<?php
// Database connection
require_once 'config.php';

// Function to generate realistic transaction ID
function generateTransactionId($id) {
    $prefix = 'TRX-';
    $random = str_pad($id, 12, '0', STR_PAD_LEFT);
    return $prefix . $random;
}

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';

// Base query
$query = "SELECT d.id, d.amount, d.donation_type, d.donation_date,
                 CONCAT(u.fname, ' ', u.lname) as user_name, u.email, u.phone, u.picture,
                 c.name as campaign_name
          FROM donations d
          JOIN users u ON d.user_id = u.id
          JOIN campaigns c ON d.campaign_id = c.id";

// Apply filter
if ($filter !== 'all') {
    switch ($filter) {
        case 'today':
            $query .= " WHERE DATE(d.donation_date) = CURDATE()";
            break;
        case 'week':
            $query .= " WHERE YEARWEEK(d.donation_date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $query .= " WHERE MONTH(d.donation_date) = MONTH(CURDATE()) AND YEAR(d.donation_date) = YEAR(CURDATE())";
            break;
        case 'mobile_banking':
        case 'credit_card':
        case 'debit_card':
        case 'bank_transfer':
            $query .= " WHERE d.donation_type = '$filter'";
            break;
    }
}

$query .= " ORDER BY d.donation_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            height: 100vh;
            overflow: hidden;
        }
        
        .app-container {
            display: flex;
            height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .header h1 {
            font-size: 24px;
            color: #2c3e50;
        }
        
        .transaction-table-container {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .transaction-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            flex: 1;
        }
        
        thead {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        tbody {
            display: block;
            overflow-y: auto;
            width: 100%;
        }
        
        tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        th {
            background-color: #f1f5f9;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        .transaction-id {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #1e40af;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-details {
            line-height: 1.4;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-email {
            font-size: 13px;
            color: #64748b;
        }
        
        .campaign-name {
            font-weight: 500;
        }
        
        .amount {
            font-weight: 600;
            color: #16a34a;
        }
        
        .donation-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .mobile_banking { background-color: #dcfce7; color: #166534; }
        .credit_card { background-color: #dbeafe; color: #1e40af; }
        .debit_card { background-color: #fef3c7; color: #92400e; }
        .bank_transfer { background-color: #f3e8ff; color: #6b21a8; }
        
        .transaction-date {
            color: #64748b;
            font-size: 14px;
        }
        
        .filter-dropdown {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: white;
            font-size: 14px;
            cursor: pointer;
        }
        
        .no-results {
            padding: 40px;
            text-align: center;
            color: #64748b;
        }
        
        /* Hide scrollbar but keep functionality */
        tbody::-webkit-scrollbar {
            display: none;
        }
        
        tbody {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="main-content">
            <div class="header">
                <h1>Transaction History</h1>
                <form method="get">
                    <select class="filter-dropdown" name="filter" onchange="this.form.submit()">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Transactions</option>
                        <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $filter === 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo $filter === 'month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="mobile_banking" <?php echo $filter === 'mobile_banking' ? 'selected' : ''; ?>>Mobile Banking</option>
                        <option value="credit_card" <?php echo $filter === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                        <option value="debit_card" <?php echo $filter === 'debit_card' ? 'selected' : ''; ?>>Debit Card</option>
                        <option value="bank_transfer" <?php echo $filter === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </form>
            </div>
            
            <div class="transaction-table-container">
                <div class="transaction-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>User</th>
                                <th>Campaign</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): 
                                    $transactionId = generateTransactionId($row['id']);
                                ?>
                                <tr>
                                    <td class="transaction-id"><?php echo $transactionId; ?></td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php if (!empty($row['picture'])): ?>
                                                    <img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="User Avatar">
                                                <?php else: ?>
                                                    <?php 
                                                        $initials = substr($row['user_name'], 0, 1) . 
                                                                    substr($row['user_name'], strpos($row['user_name'], ' ') + 1, 1);
                                                    ?>
                                                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                                        <?php echo strtoupper($initials); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name"><?php echo htmlspecialchars($row['user_name']); ?></div>
                                                <div class="user-email"><?php echo htmlspecialchars($row['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="campaign-name"><?php echo htmlspecialchars($row['campaign_name']); ?></td>
                                    <td class="amount">$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td>
                                        <span class="donation-type <?php echo $row['donation_type']; ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($row['donation_type'])); ?>
                                        </span>
                                    </td>
                                    <td class="transaction-date"><?php echo date('M j, Y h:i A', strtotime($row['donation_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-results">
                                        No transactions found matching your criteria.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>