<?php
session_start();
require 'config.php'; // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT fname, lname, email,phone ,location, picture FROM users WHERE id = ?");
$query->bind_param("i", $user_id); // Bind the parameter
$query->execute(); // Execute the query
$result = $query->get_result(); // Get the result set
$user = $result->fetch_assoc(); // Fetch the user data

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update Name, Email & Location
    if (isset($_POST['update_profile'])) {
        $fname = htmlspecialchars($_POST['fname']);
        $lname = htmlspecialchars($_POST['lname']);
        $email = htmlspecialchars($_POST['email']);
        $location = htmlspecialchars($_POST['location']);
        $phone =htmlspecialchars($_POST['phone']);

        $updateQuery = $conn->prepare("UPDATE users SET fname = ?, lname = ?, email = ?, location = ?,phone =? WHERE id = ?");
        $updateQuery->bind_param("sssssi", $fname, $lname, $email, $location, $phone , $user_id); // Bind parameters
        $updateQuery->execute(); // Execute the query

        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        // Verify old password
        $passQuery = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $passQuery->bind_param("i", $user_id); // Bind the parameter
        $passQuery->execute(); // Execute the query
        $passQuery->store_result(); // Store the result
        $passQuery->bind_result($stored_password); // Bind the result to a variable
        $passQuery->fetch(); // Fetch the result

        if (password_verify($current_password, $stored_password)) {
            $updatePass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updatePass->bind_param("si", $new_password, $user_id); // Bind parameters
            $updatePass->execute(); // Execute the query

            $_SESSION['success'] = "Password changed successfully!";
        } else {
            $_SESSION['error'] = "Incorrect current password!";
        }
        header("Location: profile.php");
        exit();
    }

     }
    
// Update Profile Picture
if (isset($_POST['upload_picture']) && isset($_FILES['picture'])) {
    $img_name = $_FILES['picture']['name'];
    $img_tmp = $_FILES['picture']['tmp_name'];
    $img_size = $_FILES['picture']['size'];
    $img_type = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));

    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($img_type, $allowed_types)) {
        $_SESSION['error'] = "Only JPG, JPEG, PNG, and GIF files are allowed!";
    } elseif ($img_size > 5 * 1024 * 1024) { // 5MB limit
        $_SESSION['error'] = "File size must be less than 5MB!";
    } else {
        // Create uploads folder if not exists
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Create unique file name to avoid conflicts
        $new_file_name = "user_" . $user_id . "_" . time() . "." . $img_type;
        $img_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($img_tmp, $img_path)) {
            // Store in Database
            $updatePic = $conn->prepare("UPDATE users SET picture = ? WHERE id = ?");
            $updatePic->bind_param("si", $img_path, $user_id);
            if ($updatePic->execute()) {
                $_SESSION['success'] = "Profile picture updated!";
            } else {
                $_SESSION['error'] = "Database update failed!";
            }
        } else {
            $_SESSION['error'] = "Failed to upload image!";
        }
    }
    header("Location: profile.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CrisisLink</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .profile-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #6c757d;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 5px;
        }

        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .btn-submit {
            width: 100%;
            border-radius: 5px;
        }

        .section-heading {
            margin-top: 30px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }

        .action-btns {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="profile-header text-center">
                    <img src="<?php echo htmlspecialchars($user['picture'] ?: 'default.png'); ?>" alt="Profile Picture" class="profile-img mb-3">
                    <h2 class="mb-2"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></h2>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <!-- Display Success & Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <!-- Profile Update Form -->
                <div class="form-container">
                    <h3 class="section-heading">Update Profile Information</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone No</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary btn-submit">Update Profile</button>
                    </form>
                </div>

                <div class="form-container mt-4">
                    <h3 class="section-heading">Change Password</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning btn-submit">Change Password</button>
                    </form>
                </div>

                <div class="form-container mt-4">
                    <h3 class="section-heading">Upload Profile Picture</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="picture" class="form-control" accept="image/*" required>
                        </div>
                        <button type="submit" name="upload_picture" class="btn btn-success btn-submit">Upload Picture</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
