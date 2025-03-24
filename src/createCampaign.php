<?php
// Include the database configuration file
require_once 'config.php';

// Check if the user is logged in and fetch user role
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php'); // Redirect to login page if the user is not logged in
    exit;
}

$user_id = $_SESSION['user_id']; // Assuming the user ID is stored in session
$user_role = $_SESSION['user_type']; // Assuming the user role (admin/user) is stored in session

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

        // Check the file type (for example, allow only jpg, png, jpeg)
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($image_ext), $allowed_ext)) {
            echo "Invalid image format. Please upload JPG, JPEG, or PNG files.";
            exit;
        }

        // Sanitize the campaign name to use it as the filename
        $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($name)); // Sanitize campaign name

        // Generate a unique filename based on the campaign name
        $image_new_name = $sanitized_name . '.' . $image_ext;

        // Define the directory to store images
        $upload_dir = 'uploads/';

        // Ensure the uploads directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);  // Create the uploads directory if it doesn't exist
        }

        // Move the uploaded image to the upload directory
        $image_path = $upload_dir . $image_new_name;
        move_uploaded_file($image_tmp_name, $image_path);
    }

    // Set the campaign approval status based on user role
    $status = ($user_role == 'admin') ? 'approved' : 'pending'; // If admin, auto-approve, else pending approval
    
    // Insert campaign data into the database (include image URL)
    $stmt = $conn->prepare("INSERT INTO campaigns (name, description, goal, category, image_url, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $name, $description, $goal, $category, $image_path, $status);
    
    if ($stmt->execute()) {
        // Redirect to the campaigns page after successful creation
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
    <style>
        /* General styles and resets */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            font-size: 1.8rem;
            color: #2c3e50;
        }

        .back-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }

        /* Form styles */
        .create-campaign-form {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        form {
            display: grid;
            grid-gap: 20px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        input[type="file"] {
            padding: 10px 0;
        }

        .file-help {
            display: block;
            margin-top: 5px;
            font-size: 0.8rem;
            color: #666;
        }

        button[type="submit"] {
            padding: 12px 24px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            justify-self: start;
        }

        button[type="submit"]:hover {
            background-color: #219653;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .create-campaign-form {
                padding: 20px 15px;
            }

            input[type="text"],
            input[type="number"],
            textarea {
                padding: 10px;
            }

            button[type="submit"] {
                width: 100%;
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px 10px;
            }

            .header h1 {
                font-size: 1.3rem;
            }

            .back-btn {
                width: 100%;
                text-align: center;
            }

            form {
                grid-gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create New Campaign</h1>
            <a href="campaign.php" class="back-btn">Back to Campaigns</a>
        </div>

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
                    <label for="image_url">Image Upload</label>
                    <input type="file" id="image_url" name="image_url" accept="image/jpeg,image/png,image/jpg" required>
                    <small class="file-help">Please upload JPG, JPEG, or PNG files only</small>
                </div>

                <button type="submit">Create Campaign</button>
            </form>
        </div>
    </div>
</body>
</html>
