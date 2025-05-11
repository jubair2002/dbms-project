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
$query = $conn->prepare("SELECT fname, lname, email, phone, location, picture FROM users WHERE id = ?");
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
        $phone = htmlspecialchars($_POST['phone']);

        $updateQuery = $conn->prepare("UPDATE users SET fname = ?, lname = ?, email = ?, location = ?, phone = ? WHERE id = ?");
        $updateQuery->bind_param("sssssi", $fname, $lname, $email, $location, $phone, $user_id); // Bind parameters
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ProfileHub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-red: #e63946;
            --dark-color: #1d1d1d;
            --light-color: #f8f9fa;
            --secondary-red: #d62828;
            --accent-gray: #6c757d;
        }
        
        body {
            background-color: #f0f2f5;
            color: var(--dark-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .success-alert {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 5px;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
        
        .error-alert {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-radius: 5px;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
        
        .profile-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .profile-header {
            background-color: var(--dark-color);
            padding: 40px 20px;
            text-align: center;
            color: var(--light-color);
            position: relative;
        }
        
        .profile-img-container {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--light-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .camera-icon {
            position: absolute;
            right: 10px;
            bottom: 10px;
            background-color: var(--primary-red);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .profile-name {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .profile-email {
            color: #ccc;
            font-size: 1rem;
        }
        
        .profile-tabs {
            background-color: var(--dark-color);
            padding: 0 20px;
        }
        
        .nav-tabs {
            border-bottom: none;
        }
        
        .nav-tabs .nav-link {
            color: #ccc;
            border: none;
            padding: 15px 20px;
            border-radius: 0;
            font-weight: 500;
            position: relative;
        }
        
        .nav-tabs .nav-link.active {
            color: white;
            background-color: transparent;
            border: none;
        }
        
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-red);
        }
        
        .form-container {
            padding: 30px;
            background-color: white;
        }
        
        .section-heading {
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        
        .form-control:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.25rem rgba(230, 57, 70, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-red);
            border-color: var(--secondary-red);
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            padding: 10px 20px;
            font-weight: 500;
            color: #212529;
        }
        
        .btn-success {
            background-color: #198754;
            border-color: #198754;
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .edit-icon {
            background-color: var(--primary-red);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            float: right;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 30px 15px;
            }
            
            .profile-img {
                width: 120px;
                height: 120px;
            }
            
            .profile-name {
                font-size: 1.5rem;
            }
            
            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    

    <div class="container">
        <!-- Success & Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="profile-container">
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-img-container">
                            <img src="<?php echo htmlspecialchars($user['picture'] ?: 'default.png'); ?>" alt="Profile Picture" class="profile-img">
                            <label for="picture-upload" class="camera-icon">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                        <h2 class="profile-name"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <!-- Profile Tabs -->
                    <div class="profile-tabs">
                        <ul class="nav nav-tabs" id="profileTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-info" type="button" role="tab" aria-controls="profile-info" aria-selected="true">Profile Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">Security</button>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Profile Info Tab -->
                        <div class="tab-pane fade show active" id="profile-info" role="tabpanel" aria-labelledby="profile-tab">
                            <div class="form-container">
                                <h3 class="section-heading">
                                    Personal Information
                                    <span class="edit-icon"><i class="fas fa-pen"></i></span>
                                </h3>
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Location</label>
                                            <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                            
                            <div class="form-container border-top">
                                <h3 class="section-heading">
                                    Profile Picture
                                    <span class="edit-icon"><i class="fas fa-camera"></i></span>
                                </h3>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Upload New Picture</label>
                                        <input type="file" id="picture-upload" name="picture" class="form-control" accept="image/*" required>
                                        <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF (Max: 5MB)</div>
                                    </div>
                                    <button type="submit" name="upload_picture" class="btn btn-success">Upload Picture</button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <div class="form-container">
                                <h3 class="section-heading">
                                    Change Password
                                    <span class="edit-icon"><i class="fas fa-lock"></i></span>
                                </h3>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Bootstrap tabs
        var triggerTabList = [].slice.call(document.querySelectorAll('#profileTab button'))
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    </script>
</body>
</html>