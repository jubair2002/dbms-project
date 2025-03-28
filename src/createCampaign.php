<?php
require_once 'config.php';

// Check if the user is logged in and fetch user role
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_type'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $goal = $_POST['goal'];
    $category = $_POST['category'];
    
    // Handle the uploaded image
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $image_tmp_name = $_FILES['image_url']['tmp_name'];
        $image_name = $_FILES['image_url']['name'];
        $image_size = $_FILES['image_url']['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($image_ext), $allowed_ext)) {
            echo "Invalid image format. Please upload JPG, JPEG, or PNG files.";
            exit;
        }

        $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($name));
        $image_new_name = $sanitized_name . '.' . $image_ext;
        $upload_dir = 'uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_path = $upload_dir . $image_new_name;
        move_uploaded_file($image_tmp_name, $image_path);
    }

    $status = ($user_role == 'admin') ? 'approved' : 'pending';
    
    $stmt = $conn->prepare("INSERT INTO campaigns (name, description, goal, category, image_url, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $name, $description, $goal, $category, $image_path, $status);
    
    if ($stmt->execute()) {
        header("Location: campaign.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Campaign</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #ff0000;
            --light-color: #ffffff;
            --gray-color: #cccccc;
            --border-radius: 4px;
            --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent-color);
        }

        .header h1 {
            font-size: 24px;
            color: var(--primary-color);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: var(--light-color);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .back-btn:hover {
            background-color: var(--secondary-color);
        }

        .create-campaign-form {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
        }

        form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gray-color);
            border-radius: var(--border-radius);
            font-family: inherit;
            transition: var(--transition);
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.1);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        input[type="file"] {
            padding: 10px 0;
            width: 100%;
        }

        .file-help {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: var(--secondary-color);
        }

        button[type="submit"] {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: var(--accent-color);
            color: var(--light-color);
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            justify-self: start;
        }

        button[type="submit"]:hover {
            background-color: #cc0000;
        }

        .status-notice {
            padding: 12px;
            background-color: #ffeeee;
            border-left: 3px solid var(--accent-color);
            margin-bottom: 20px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .create-campaign-form {
                padding: 20px;
            }

            button[type="submit"] {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }

            .header h1 {
                font-size: 20px;
            }

            form {
                gap: 15px;
            }

            input[type="text"],
            input[type="number"],
            textarea,
            select {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plus-circle"></i> Create New Campaign</h1>
            <a href="campaign.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>

        <?php if($user_role != 'admin'): ?>
            <div class="status-notice">
                <i class="fas fa-info-circle"></i> Your campaign will be reviewed by an admin before approval.
            </div>
        <?php endif; ?>

        <div class="create-campaign-form">
            <form method="POST" action="createCampaign.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Campaign Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="goal">Goal Amount</label>
                    <input type="number" id="goal" name="goal" min="1" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" required>
                </div>

                <div class="form-group">
                    <label for="image_url">Campaign Image</label>
                    <input type="file" id="image_url" name="image_url" accept="image/jpeg,image/png,image/jpg" required>
                    <small class="file-help">Upload JPG, JPEG, or PNG files (Max 5MB)</small>
                </div>

                <button type="submit">
                    <i class="fas fa-rocket"></i> Create Campaign
                </button>
            </form>
        </div>
    </div>
</body>
</html>