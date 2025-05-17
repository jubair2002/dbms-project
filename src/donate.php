<?php
require_once 'config.php';
session_start();

if (!isset($_GET['campaign_id']) || empty($_GET['campaign_id'])) {
    header('Location: campaign.php');
    exit;
}

$campaign_id = intval($_GET['campaign_id']);
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

// Fetch campaign details
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: campaign.php');
    exit;
}

$campaign = $result->fetch_assoc();

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']);
$user_id = $user_logged_in ? $_SESSION['user_id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate to <?php echo htmlspecialchars($campaign['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #D10000;
            /* Red */
            --secondary-color: #990000;
            /* Darker red */
            --dark-color: #222;
            /* Dark gray/black */
            --light-color: #fff;
            /* White */
            --border-color: #e0e0e0;
        }

        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .header h1 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        .back-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--dark-color);
            border: none;
            border-radius: 4px;
            color: var(--light-color);
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #111;
        }

        .donation-container {
            background-color: var(--light-color);
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid var(--border-color);
        }

        .campaign-info {
            display: flex;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }

        .campaign-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .campaign-details h2 {
            margin-top: 0;
            margin-bottom: 10px;
            color: var(--dark-color);
        }

        .donation-amount {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .donation-form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .payment-method {
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 12px 15px;
            cursor: pointer;
            flex: 1 0 45%;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .payment-method input[type="radio"] {
            margin-right: 10px;
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: rgba(209, 0, 0, 0.05);
            box-shadow: 0 0 0 1px var(--primary-color);
        }

        .row {
            display: flex;
            gap: 20px;
        }

        .col {
            flex: 1;
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .submit-btn:hover {
            background-color: var(--secondary-color);
        }

        .payment-fields {
            margin-top: 20px;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .payment-fields.active {
            display: block;
        }

        .login-message {
            background-color: #FFF3F3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-left: 4px solid var(--primary-color);
        }

        .login-message i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .login-message a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            margin-left: 5px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .row {
                flex-direction: column;
            }

            .payment-method {
                flex: 1 0 100%;
            }

            .campaign-info {
                flex-direction: column;
            }

            .campaign-image {
                margin-bottom: 15px;
                margin-right: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Make a Donation</h1>
            <a href="campaign-details.php?id=<?php echo $campaign_id; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Campaign
            </a>
        </div>

        <div class="donation-container">
            <?php if (!$user_logged_in): ?>
                <div class="login-message">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Note:</strong> For a better donation experience, consider
                        <a href="login.php?redirect=donate.php?campaign_id=<?php echo $campaign_id; ?>&amount=<?php echo $amount; ?>">logging in</a> or
                        <a href="register.php?redirect=donate.php?campaign_id=<?php echo $campaign_id; ?>&amount=<?php echo $amount; ?>">creating an account</a>.
                    </div>
                </div>
            <?php endif; ?>

            <div class="campaign-info">
                <div class="campaign-image">
                    <img src="<?php echo htmlspecialchars($campaign['image_url']); ?>" alt="<?php echo htmlspecialchars($campaign['name']); ?>">
                </div>
                <div class="campaign-details">
                    <h2><?php echo htmlspecialchars($campaign['name']); ?></h2>
                    <p>You are donating: <span class="donation-amount">$<span id="display-amount"><?php echo number_format($amount, 2); ?></span></span></p>
                </div>
            </div>

            <form action="process-donation.php" method="post" id="donation-form">
                <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">

                <div class="form-group">
                    <label for="amount">Donation Amount ($)</label>
                    <input type="number" id="amount" name="amount" min="1" step="0.01" value="<?php echo $amount; ?>" required>
                </div>

                <?php if (!$user_logged_in): ?>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-methods">
                        <div class="payment-method" data-method="credit_card">
                            <input type="radio" name="payment_method" value="credit_card" id="credit_card" required>
                            <label for="credit_card">Credit Card</label>
                        </div>
                        <div class="payment-method" data-method="debit_card">
                            <input type="radio" name="payment_method" value="debit_card" id="debit_card">
                            <label for="debit_card">Debit Card</label>
                        </div>
                        <div class="payment-method" data-method="mobile_banking">
                            <input type="radio" name="payment_method" value="mobile_banking" id="mobile_banking">
                            <label for="mobile_banking">Mobile Banking</label>
                        </div>
                        <div class="payment-method" data-method="bank_transfer">
                            <input type="radio" name="payment_method" value="bank_transfer" id="bank_transfer">
                            <label for="bank_transfer">Bank Transfer</label>
                        </div>
                    </div>
                </div>

                <!-- Credit/Debit Card Fields -->
                <div class="payment-fields" id="card-fields">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="ex: 1234 5678 9012 3456">
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="expiry">Expiry Date (MM/YY)</label>
                                <input type="text" id="expiry" name="expiry" placeholder="MM/YY">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Banking Fields -->
                <div class="payment-fields" id="mobile-fields">
                    <div class="form-group">
                        <label for="mobile_provider">Mobile Banking Provider</label>
                        <select id="mobile_provider" name="mobile_provider">
                            <option value="">Select Provider</option>
                            <option value="Bkash">Bkash</option>
                            <option value="Nagad">Nagad</option>
                            <option value="Rocket">Rocket</option>
                            <option value="Upay">Upay</option>
                            <option value="SureCash">SureCash</option>
                            <option value="DBBL">DBBL</option>
                            <option value="MyCash">MyCash</option>

                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number</label>
                        <input type="text" id="mobile_number" name="mobile_number" placeholder="Your registered mobile number">
                    </div>
                </div>

                <!-- Bank Transfer Fields -->
                <div class="payment-fields" id="bank-fields">
                    <div class="form-group">
                        <label for="bank_name">Bank Name</label>
                        <select id="bank_name" name="bank_name">
                            <option value="">Select Bank</option>
                            <option value="aibl">Al-Arafah Islami Bank PLC</option>
                            <option value="dutch_bangla">Dutch-Bangla Bank</option>
                            <option value="brac">BRAC Bank</option>
                            <option value="city">City Bank</option>
                            <option value="eastern">Eastern Bank (EBL)</option>
                            <option value="islami">Islami Bank Bangladesh</option>
                            <option value="standard_chartered">Standard Chartered</option>
                            <option value="hsbc">HSBC Bangladesh</option>
                            <option value="sonali">Sonali Bank</option>
                            <option value="janata">Janata Bank</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number" placeholder="Your account number">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Complete Donation</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update the display amount when the input changes
            const amountInput = document.getElementById('amount');
            const displayAmount = document.getElementById('display-amount');

            amountInput.addEventListener('input', function() {
                displayAmount.textContent = parseFloat(this.value).toFixed(2);
            });

            // Payment method selection
            const paymentMethods = document.querySelectorAll('.payment-method');
            const paymentFields = {
                'credit_card': document.getElementById('card-fields'),
                'debit_card': document.getElementById('card-fields'),
                'mobile_banking': document.getElementById('mobile-fields'),
                'bank_transfer': document.getElementById('bank-fields')
            };

            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    // Select the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;

                    // Highlight the selected method
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    this.classList.add('selected');

                    // Show the appropriate fields
                    const methodName = this.dataset.method;
                    Object.values(paymentFields).forEach(field => field.classList.remove('active'));

                    if (paymentFields[methodName]) {
                        paymentFields[methodName].classList.add('active');
                    }

                    // Enable/disable required fields based on payment method
                    toggleRequiredFields(methodName);
                });
            });

            // Form validation
            document.getElementById('donation-form').addEventListener('submit', function(e) {
                const selectedMethod = document.querySelector('input[name="payment_method"]:checked');

                if (!selectedMethod) {
                    alert('Please select a payment method');
                    e.preventDefault();
                    return;
                }

                // Additional validation could be added here
            });

            // Toggle required fields based on payment method
            function toggleRequiredFields(method) {
                // Card fields
                const cardFields = ['card_number', 'expiry', 'cvv'];
                cardFields.forEach(field => {
                    document.getElementById(field).required = (method === 'credit_card' || method === 'debit_card');
                });

                // Mobile banking fields
                const mobileFields = ['mobile_provider', 'mobile_number'];
                mobileFields.forEach(field => {
                    document.getElementById(field).required = (method === 'mobile_banking');
                });

                // Bank transfer fields
                const bankFields = ['bank_name', 'account_number'];
                bankFields.forEach(field => {
                    document.getElementById(field).required = (method === 'bank_transfer');
                });
            }
        });
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>