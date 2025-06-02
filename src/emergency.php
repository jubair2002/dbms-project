<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$message = '';
$error = '';

// Blood type compatibility logic
function getCompatibleDonors($requestedBloodType) {
    $compatibility = [
        'A+' => ['A+', 'A-', 'O+', 'O-'],
        'A-' => ['A-', 'O-'],
        'B+' => ['B+', 'B-', 'O+', 'O-'],
        'B-' => ['B-', 'O-'],
        'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], // Universal recipient
        'AB-' => ['A-', 'B-', 'AB-', 'O-'],
        'O+' => ['O+', 'O-'],
        'O-' => ['O-']
    ];
    return $compatibility[$requestedBloodType] ?? [];
}

function canDonateToBloodType($donorType, $requestedType) {
    $compatibleDonors = getCompatibleDonors($requestedType);
    return in_array($donorType, $compatibleDonors);
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax'] === 'search_donors') {
        $blood_type = $_GET['blood_type'] ?? '';
        $location = $_GET['location'] ?? '';
        $name = $_GET['name'] ?? '';
        $availability = $_GET['availability'] ?? '';
        
        $query = "
            SELECT u.fname, u.lname, u.email, u.phone, u.location, 
                   bd.blood_type, bd.availability_status, bd.last_donation_date,
                   DATEDIFF(NOW(), bd.last_donation_date) as days_since_donation
            FROM users u 
            JOIN blood_donors bd ON u.id = bd.user_id 
            WHERE bd.donor_status = 'active'
        ";
        
        $params = [];
        $types = "";
        
        if (!empty($blood_type)) {
            $query .= " AND bd.blood_type = ?";
            $params[] = $blood_type;
            $types .= "s";
        }
        
        if (!empty($location)) {
            $query .= " AND u.location LIKE ?";
            $params[] = "%$location%";
            $types .= "s";
        }
        
        if (!empty($name)) {
            $query .= " AND (u.fname LIKE ? OR u.lname LIKE ?)";
            $params[] = "%$name%";
            $params[] = "%$name%";
            $types .= "ss";
        }
        
        if (!empty($availability)) {
            $query .= " AND bd.availability_status = ?";
            $params[] = $availability;
            $types .= "s";
        }
        
        $query .= " ORDER BY 
                      CASE bd.availability_status 
                        WHEN 'available' THEN 1 
                        WHEN 'unavailable' THEN 2 
                        ELSE 3 
                      END, 
                      u.fname ASC 
                    LIMIT 50";
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $donors = [];
            while ($row = $result->fetch_assoc()) {
                $donors[] = [
                    'fname' => htmlspecialchars($row['fname']),
                    'lname' => htmlspecialchars($row['lname']),
                    'email' => htmlspecialchars($row['email']),
                    'phone' => htmlspecialchars($row['phone']),
                    'location' => htmlspecialchars($row['location']),
                    'blood_type' => htmlspecialchars($row['blood_type']),
                    'availability_status' => htmlspecialchars($row['availability_status']),
                    'days_since_donation' => (int)($row['days_since_donation'] ?? 0),
                    'last_donation_date' => $row['last_donation_date']
                ];
            }
            $stmt->close();
            echo json_encode($donors);
        } else {
            echo json_encode(['error' => 'Database error']);
        }
        exit();
    }
    
    if ($_GET['ajax'] === 'compatible_donors') {
        $blood_type = $_GET['blood_type'] ?? '';
        $location = $_GET['location'] ?? '';
        
        if (empty($blood_type)) {
            echo json_encode(['error' => 'Blood type required']);
            exit();
        }
        
        $compatibleTypes = getCompatibleDonors($blood_type);
        $placeholders = str_repeat('?,', count($compatibleTypes) - 1) . '?';
        
        $query = "
            SELECT u.fname, u.lname, u.email, u.phone, u.location, 
                   bd.blood_type, bd.availability_status, bd.last_donation_date,
                   DATEDIFF(NOW(), bd.last_donation_date) as days_since_donation
            FROM users u 
            JOIN blood_donors bd ON u.id = bd.user_id 
            WHERE bd.donor_status = 'active' 
            AND bd.blood_type IN ($placeholders)
        ";
        
        $params = $compatibleTypes;
        
        if (!empty($location)) {
            $query .= " AND u.location LIKE ?";
            $params[] = "%$location%";
        }
        
        $query .= " ORDER BY bd.availability_status DESC, u.fname ASC LIMIT 20";
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $donors = [];
            while ($row = $result->fetch_assoc()) {
                $donors[] = [
                    'fname' => htmlspecialchars($row['fname']),
                    'lname' => htmlspecialchars($row['lname']),
                    'email' => htmlspecialchars($row['email']),
                    'phone' => htmlspecialchars($row['phone']),
                    'location' => htmlspecialchars($row['location']),
                    'blood_type' => htmlspecialchars($row['blood_type']),
                    'availability_status' => htmlspecialchars($row['availability_status']),
                    'days_since_donation' => (int)($row['days_since_donation'] ?? 0)
                ];
            }
            $stmt->close();
            echo json_encode($donors);
        } else {
            echo json_encode(['error' => 'Database error']);
        }
        exit();
    }
    
    if ($_GET['ajax'] === 'stats') {
        $stats = [];
        
        // Active requests
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_requests WHERE request_status = 'open'");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stats['active_requests'] = (int)$row['count'];
            $stmt->close();
        }
        
        // Total donors
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_donors WHERE donor_status = 'active'");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stats['total_donors'] = (int)$row['count'];
            $stmt->close();
        }
        
        // Critical requests
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_requests WHERE urgency = 'critical' AND request_status = 'open'");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stats['critical_requests'] = (int)$row['count'];
            $stmt->close();
        }
        
        // Available donors
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_donors WHERE donor_status = 'active' AND availability_status = 'available'");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stats['available_donors'] = (int)$row['count'];
            $stmt->close();
        }
        
        // Blood type distribution
        $stmt = $conn->prepare("SELECT blood_type, COUNT(*) as count FROM blood_donors WHERE donor_status = 'active' GROUP BY blood_type");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $blood_types = [];
            while ($row = $result->fetch_assoc()) {
                $blood_types[$row['blood_type']] = (int)$row['count'];
            }
            $stats['blood_types'] = $blood_types;
            $stmt->close();
        }
        
        echo json_encode($stats);
        exit();
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'register_donor':
            $blood_type = trim($_POST['blood_type'] ?? '');
            $age = (int)($_POST['age'] ?? 0);
            $weight = (float)($_POST['weight'] ?? 0);
            $medical_conditions = trim($_POST['medical_conditions'] ?? '');
            $emergency_contact = trim($_POST['emergency_contact'] ?? '');
            
            // Basic validation
            $valid_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            if (!in_array($blood_type, $valid_types)) {
                $error = "Invalid blood type";
            } elseif ($age < 18 || $age > 65) {
                $error = "Age must be between 18-65 years";
            } elseif ($weight < 50) {
                $error = "Minimum weight: 50kg";
            } elseif (empty($emergency_contact)) {
                $error = "Emergency contact required";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO blood_donors (user_id, blood_type, age, weight, medical_conditions, emergency_contact, availability_status, donor_status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'available', 'active')
                    ON DUPLICATE KEY UPDATE 
                    blood_type = VALUES(blood_type),
                    age = VALUES(age),
                    weight = VALUES(weight),
                    medical_conditions = VALUES(medical_conditions),
                    emergency_contact = VALUES(emergency_contact),
                    availability_status = 'available',
                    updated_at = NOW()
                ");
                
                if ($stmt) {
                    $stmt->bind_param("isiiss", $user_id, $blood_type, $age, $weight, $medical_conditions, $emergency_contact);
                    if ($stmt->execute()) {
                        $message = "Donor registration successful! You can now help save lives.";
                        header("Location: emergency.php?tab=register&msg=success");
                        exit();
                    } else {
                        $error = "Registration failed";
                    }
                    $stmt->close();
                } else {
                    $error = "Database error";
                }
            }
            break;
            
        case 'create_request':
            $blood_type = trim($_POST['blood_type'] ?? '');
            $units = (int)($_POST['units_needed'] ?? 1);
            $urgency = trim($_POST['urgency'] ?? '');
            $patient_name = trim($_POST['patient_name'] ?? '');
            $hospital = trim($_POST['hospital_name'] ?? '');
            $address = trim($_POST['hospital_address'] ?? '');
            $contact = trim($_POST['contact_number'] ?? '');
            $date = trim($_POST['needed_by_date'] ?? '');
            $notes = trim($_POST['additional_notes'] ?? '');
            
            // Basic validation
            $valid_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            $valid_urgencies = ['low', 'medium', 'high', 'critical'];
            
            if (!in_array($blood_type, $valid_types)) {
                $error = "Invalid blood type";
            } elseif ($units < 1 || $units > 10) {
                $error = "Units must be between 1-10";
            } elseif (!in_array($urgency, $valid_urgencies)) {
                $error = "Invalid urgency level";
            } elseif (empty($patient_name) || empty($hospital) || empty($address) || empty($contact) || empty($date)) {
                $error = "All required fields must be filled";
            } elseif (strtotime($date) < strtotime('today')) {
                $error = "Date cannot be in the past";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO blood_requests 
                    (requester_id, blood_type, units_needed, urgency, patient_name, hospital_name, hospital_address, contact_number, needed_by_date, additional_notes, request_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')
                ");
                
                if ($stmt) {
                    $stmt->bind_param("isiissssss", $user_id, $blood_type, $units, $urgency, $patient_name, $hospital, $address, $contact, $date, $notes);
                    if ($stmt->execute()) {
                        $request_id = $conn->insert_id;
                        $message = "Blood request created successfully! Request ID: #$request_id";
                    } else {
                        $error = "Failed to create request";
                    }
                    $stmt->close();
                } else {
                    $error = "Database error";
                }
            }
            break;
            
        case 'help_request':
            $request_id = (int)($_POST['request_id'] ?? 0);
            $help_type = trim($_POST['help_type'] ?? '');
            $response_message = trim($_POST['response_message'] ?? '');
            
            $valid_help_types = ['interested', 'can_donate', 'can_coordinate', 'maybe'];
            
            if ($request_id <= 0) {
                $error = "Invalid request ID";
            } elseif (!in_array($help_type, $valid_help_types)) {
                $error = "Invalid help type";
            } else {
                // For donors, check if they're registered and compatible
                $can_help = true;
                $donor_id = null;
                
                if ($help_type === 'can_donate') {
                    // Check if user is registered donor
                    $stmt = $conn->prepare("SELECT id, blood_type FROM blood_donors WHERE user_id = ? AND donor_status = 'active'");
                    if ($stmt) {
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $donor_id = $row['id'];
                            
                            // Check blood compatibility
                            $stmt2 = $conn->prepare("SELECT blood_type FROM blood_requests WHERE id = ?");
                            if ($stmt2) {
                                $stmt2->bind_param("i", $request_id);
                                $stmt2->execute();
                                $result2 = $stmt2->get_result();
                                if ($req_row = $result2->fetch_assoc()) {
                                    if (!canDonateToBloodType($row['blood_type'], $req_row['blood_type'])) {
                                        $can_help = false;
                                        $error = "Your blood type is not compatible with this request";
                                    }
                                }
                                $stmt2->close();
                            }
                        } else {
                            $can_help = false;
                            $error = "You must be a registered donor to donate blood";
                        }
                        $stmt->close();
                    }
                }
                
                if ($can_help) {
                    // Insert or update response
                    if ($donor_id) {
                        // Donor response
                        $stmt = $conn->prepare("
                            INSERT INTO donation_responses (request_id, donor_id, response_status, response_message) 
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                            response_status = VALUES(response_status),
                            response_message = VALUES(response_message),
                            updated_at = NOW()
                        ");
                        if ($stmt) {
                            $stmt->bind_param("iiss", $request_id, $donor_id, $help_type, $response_message);
                            if ($stmt->execute()) {
                                $message = "Response submitted successfully! Thank you for helping save lives.";
                            } else {
                                $error = "Failed to submit response";
                            }
                            $stmt->close();
                        }
                    } else {
                        // General helper response (stored with donor_id = 0 for non-donors)
                        $stmt = $conn->prepare("
                            INSERT INTO donation_responses (request_id, donor_id, response_status, response_message) 
                            VALUES (?, 0, ?, ?)
                        ");
                        if ($stmt) {
                            $stmt->bind_param("iss", $request_id, $help_type, $response_message);
                            if ($stmt->execute()) {
                                $message = "Thank you for offering to help! Your response has been recorded.";
                            } else {
                                $error = "Failed to submit response";
                            }
                            $stmt->close();
                        }
                    }
                }
            }
            break;
            
        case 'update_request_status':
            $request_id = (int)($_POST['request_id'] ?? 0);
            $new_status = trim($_POST['new_status'] ?? '');
            
            $valid_statuses = ['open', 'partially_fulfilled', 'fulfilled', 'expired', 'cancelled'];
            
            if ($request_id <= 0) {
                $error = "Invalid request ID";
            } elseif (!in_array($new_status, $valid_statuses)) {
                $error = "Invalid status";
            } else {
                // Verify user owns this request
                $stmt = $conn->prepare("SELECT id FROM blood_requests WHERE id = ? AND requester_id = ?");
                if ($stmt) {
                    $stmt->bind_param("ii", $request_id, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $stmt->close();
                        
                        $stmt = $conn->prepare("UPDATE blood_requests SET request_status = ?, updated_at = NOW() WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param("si", $new_status, $request_id);
                            if ($stmt->execute()) {
                                $message = "Request status updated to " . ucfirst(str_replace('_', ' ', $new_status));
                            } else {
                                $error = "Failed to update status";
                            }
                            $stmt->close();
                        }
                    } else {
                        $error = "You can only update your own requests";
                        $stmt->close();
                    }
                }
            }
            break;
    }
}

// Get user donor status
$donor_info = null;
$is_donor = false;

$stmt = $conn->prepare("
    SELECT *, DATEDIFF(NOW(), last_donation_date) as days_since_donation 
    FROM blood_donors 
    WHERE user_id = ? AND donor_status = 'active'
");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $is_donor = true;
        $donor_info = $result->fetch_assoc();
        
        // Auto-update availability status based on 90-day rule
        if ($donor_info['last_donation_date'] && $donor_info['days_since_donation'] >= 90 && $donor_info['availability_status'] == 'recently_donated') {
            $update_stmt = $conn->prepare("UPDATE blood_donors SET availability_status = 'available' WHERE user_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("i", $user_id);
                $update_stmt->execute();
                $donor_info['availability_status'] = 'available';
                $update_stmt->close();
            }
        }
    }
    $stmt->close();
}

// Check for success message from redirect
if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $message = "Donor registration successful! You can now help save lives.";
}

// Determine active tab
$activeTab = $_GET['tab'] ?? 'requests';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Blood Service - Save Lives Together</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --dark-color: #212529;
            --light-bg: #ffffff;
            --border-color: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden;
            height: 100%;
        }

        body {
            background: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--dark-color);
        }

        .container-fluid {
            padding: 1rem;
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            background: white;
            margin-bottom: 1rem;
        }

        .blood-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: var(--dark-color);
        }

        .stat-number.text-danger { color: var(--primary-color); }

        .stat-label {
            color: #6c757d;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-tabs {
            border: none;
            background: white;
            border-radius: 8px;
            padding: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 6px;
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .nav-tabs .nav-link:hover {
            background: #f8f9fa;
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #c82333;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid var(--border-color);
            padding: 0.5rem 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .alert {
            border: none;
            border-radius: 6px;
            padding: 0.75rem 1rem;
        }

        .request-card {
            border-left: 4px solid transparent;
            margin-bottom: 1rem;
        }

        .request-card.critical { border-left-color: var(--primary-color); }
        .request-card.high { border-left-color: #ffc107; }
        .request-card.medium { border-left-color: #17a2b8; }
        .request-card.low { border-left-color: #28a745; }

        .badge {
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .register-prompt {
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .modal-content {
            border: none;
            border-radius: 8px;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .table {
            font-size: 0.9rem;
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
        }

        .loading-spinner.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .stat-number { font-size: 1.5rem; }
            .nav-tabs .nav-link { padding: 0.5rem 0.75rem; font-size: 0.875rem; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Alerts -->
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Registration Prompt for Non-Donors -->
        <?php if (!$is_donor): ?>
        <div class="register-prompt">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <i class="fas fa-user-plus"></i> Become a Life Saver!
                    </h5>
                    <p class="mb-0 text-muted">
                        Join our community of blood donors and help save lives in your area.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-primary" onclick="showTab('register')">
                        <i class="fas fa-heart"></i> Register as Donor
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Row -->
        <div class="row mb-3" id="statsRow">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number" id="activeRequests">-</div>
                    <div class="stat-label">Active</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-danger" id="criticalRequests">-</div>
                    <div class="stat-label">Critical</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number" id="availableDonors">-</div>
                    <div class="stat-label">Available</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number" id="totalDonors">-</div>
                    <div class="stat-label">Donors</div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="mainTabs">
            <li class="nav-item">
                <button class="nav-link <?php echo $activeTab == 'requests' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#requests">
                    <i class="fas fa-list"></i> Requests
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?php echo $activeTab == 'donors' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#donors">
                    <i class="fas fa-users"></i> Donors
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?php echo $activeTab == 'register' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#register">
                    <i class="fas fa-user-plus"></i> <?php echo $is_donor ? 'Profile' : 'Register'; ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?php echo $activeTab == 'create' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#create">
                    <i class="fas fa-plus-circle"></i> New
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?php echo $activeTab == 'dashboard' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Requests Tab -->
            <div class="tab-pane fade <?php echo $activeTab == 'requests' ? 'show active' : ''; ?>" id="requests">
                <?php
                $stmt = $conn->prepare("
                    SELECT br.*, u.fname, u.lname, u.phone,
                           (SELECT COUNT(*) FROM donation_responses dr WHERE dr.request_id = br.id) as total_helpers
                    FROM blood_requests br 
                    JOIN users u ON br.requester_id = u.id 
                    WHERE br.request_status = 'open' 
                    ORDER BY 
                        CASE br.urgency 
                            WHEN 'critical' THEN 1 
                            WHEN 'high' THEN 2 
                            WHEN 'medium' THEN 3 
                            ELSE 4 
                        END, 
                        br.needed_by_date ASC
                    LIMIT 20
                ");
                
                if ($stmt) {
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while ($request = $result->fetch_assoc()) {
                            $compatible_types = getCompatibleDonors($request['blood_type']);
                            $is_compatible = $is_donor && in_array($donor_info['blood_type'] ?? '', $compatible_types);
                ?>
                <div class="card request-card <?php echo htmlspecialchars($request['urgency']); ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-auto">
                                <div class="blood-badge"><?php echo htmlspecialchars($request['blood_type']); ?></div>
                            </div>
                            <div class="col">
                                <h6 class="mb-1">
                                    <?php echo htmlspecialchars($request['patient_name']); ?>
                                    <span class="badge bg-<?php 
                                        echo $request['urgency'] == 'critical' ? 'danger' : 
                                            ($request['urgency'] == 'high' ? 'warning' : 
                                            ($request['urgency'] == 'medium' ? 'info' : 'success')); 
                                    ?> ms-2">
                                        <?php echo ucfirst(htmlspecialchars($request['urgency'])); ?>
                                    </span>
                                    <?php if ($request['total_helpers'] > 0): ?>
                                    <span class="badge bg-success ms-2">
                                        <?php echo (int)$request['total_helpers']; ?> helpers
                                    </span>
                                    <?php endif; ?>
                                </h6>
                                <p class="mb-1 small">
                                    <i class="fas fa-hospital"></i> <?php echo htmlspecialchars($request['hospital_name']); ?> • 
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($request['hospital_address']); ?>
                                </p>
                                <div class="small text-muted">
                                    <i class="fas fa-calendar"></i> Needed by: <?php echo date('M j', strtotime($request['needed_by_date'])); ?> • 
                                    <i class="fas fa-tint"></i> Units: <?php echo (int)$request['units_needed']; ?> • 
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($request['contact_number']); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <?php if ($request['requester_id'] == $user_id): ?>
                                <button class="btn btn-sm btn-outline-secondary update-status-btn" 
                                        data-request-id="<?php echo (int)$request['id']; ?>">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm btn-primary help-btn" 
                                        data-request-id="<?php echo (int)$request['id']; ?>"
                                        data-blood-type="<?php echo htmlspecialchars($request['blood_type']); ?>"
                                        data-patient="<?php echo htmlspecialchars($request['patient_name']); ?>"
                                        data-hospital="<?php echo htmlspecialchars($request['hospital_name']); ?>"
                                        data-compatible="<?php echo $is_compatible ? 'true' : 'false'; ?>">
                                    <i class="fas fa-hand-holding-heart"></i> Help
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                        }
                    } else {
                        echo '<div class="text-center py-5">
                                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                <h5>No Active Blood Requests</h5>
                                <p class="text-muted">All current requests have been fulfilled.</p>
                              </div>';
                    }
                    $stmt->close();
                }
                ?>
            </div>

            <!-- Donors Tab -->
            <div class="tab-pane fade <?php echo $activeTab == 'donors' ? 'show active' : ''; ?>" id="donors">
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="donorSearchForm">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <select name="blood_type" class="form-select">
                                        <option value="">All Blood Types</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="location" class="form-control" placeholder="Location">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="name" class="form-control" placeholder="Name">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row" id="donorsList">
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Use the search filters above to find donors</p>
                    </div>
                </div>
            </div>

            <!-- Register Tab -->
            <div class="tab-pane fade <?php echo $activeTab == 'register' ? 'show active' : ''; ?>" id="register">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-plus"></i> 
                                    <?php echo $is_donor ? 'Your Donor Profile' : 'Become a Blood Donor'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!$is_donor): ?>
                                <form method="POST" onsubmit="return validateDonorForm()">
                                    <input type="hidden" name="action" value="register_donor">
                                    <div class="mb-3">
                                        <label class="form-label">Blood Type *</label>
                                        <select name="blood_type" class="form-select" required>
                                            <option value="">Select Blood Type</option>
                                            <option value="A+">A+</option>
                                            <option value="A-">A-</option>
                                            <option value="B+">B+</option>
                                            <option value="B-">B-</option>
                                            <option value="AB+">AB+</option>
                                            <option value="AB-">AB-</option>
                                            <option value="O+">O+</option>
                                            <option value="O-">O-</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Age *</label>
                                            <input type="number" name="age" class="form-control" min="18" max="65" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Weight (kg) *</label>
                                            <input type="number" name="weight" class="form-control" min="50" step="0.1" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Emergency Contact *</label>
                                        <input type="tel" name="emergency_contact" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Medical Conditions</label>
                                        <textarea name="medical_conditions" class="form-control" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-heart"></i> Complete Registration
                                    </button>
                                </form>
                                
                                <?php else: ?>
                                <div class="text-center mb-3">
                                    <div class="blood-badge mb-2" style="width: 60px; height: 60px; font-size: 1.2rem;">
                                        <?php echo htmlspecialchars($donor_info['blood_type']); ?>
                                    </div>
                                    <h5><?php echo htmlspecialchars($donor_info['blood_type']); ?> Donor</h5>
                                </div>
                                <div class="alert alert-success">
                                    <strong>Status:</strong> 
                                    <span class="badge bg-<?php echo $donor_info['availability_status'] == 'available' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($donor_info['availability_status']))); ?>
                                    </span>
                                </div>
                                <p><strong>Age:</strong> <?php echo (int)$donor_info['age']; ?> years</p>
                                <p><strong>Weight:</strong> <?php echo (float)$donor_info['weight']; ?> kg</p>
                                <?php if ($donor_info['last_donation_date']): ?>
                                <p><strong>Last Donation:</strong> <?php echo date('M j, Y', strtotime($donor_info['last_donation_date'])); ?></p>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Request Tab -->
            <div class="tab-pane fade <?php echo $activeTab == 'create' ? 'show active' : ''; ?>" id="create">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Create Blood Request</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" onsubmit="return validateRequestForm()">
                                    <input type="hidden" name="action" value="create_request">
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Blood Type *</label>
                                            <select name="blood_type" class="form-select" required>
                                                <option value="">Select</option>
                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Units *</label>
                                            <input type="number" name="units_needed" class="form-control" min="1" max="10" value="1" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Urgency *</label>
                                            <select name="urgency" class="form-select" required>
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                                <option value="critical">Critical</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Patient Name *</label>
                                            <input type="text" name="patient_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Needed By *</label>
                                            <input type="date" name="needed_by_date" class="form-control" 
                                                   min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Hospital Name *</label>
                                        <input type="text" name="hospital_name" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Hospital Address *</label>
                                        <input type="text" name="hospital_address" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Contact Number *</label>
                                        <input type="tel" name="contact_number" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Additional Notes</label>
                                        <textarea name="additional_notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane"></i> Create Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Tab -->
            <div class="tab-pane fade <?php echo $activeTab == 'dashboard' ? 'show active' : ''; ?>" id="dashboard">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Blood Type Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="bloodTypeChart" style="max-height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-history"></i> My Requests</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT * FROM blood_requests 
                                    WHERE requester_id = ? 
                                    ORDER BY created_at DESC 
                                    LIMIT 5
                                ");
                                
                                if ($stmt) {
                                    $stmt->bind_param("i", $user_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    if ($result->num_rows > 0) {
                                        echo '<div class="table-responsive">
                                                <table class="table table-sm">
                                                  <thead>
                                                    <tr>
                                                      <th>Date</th>
                                                      <th>Patient</th>
                                                      <th>Type</th>
                                                      <th>Status</th>
                                                    </tr>
                                                  </thead>
                                                  <tbody>';
                                        while ($req = $result->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td>' . date('M j', strtotime($req['created_at'])) . '</td>';
                                            echo '<td>' . htmlspecialchars(substr($req['patient_name'], 0, 15)) . '</td>';
                                            echo '<td><span class="badge bg-danger">' . htmlspecialchars($req['blood_type']) . '</span></td>';
                                            echo '<td><span class="badge bg-secondary">' . ucfirst(htmlspecialchars($req['request_status'])) . '</span></td>';
                                            echo '</tr>';
                                        }
                                        echo '</tbody></table></div>';
                                    } else {
                                        echo '<p class="text-center text-muted">No requests yet</p>';
                                    }
                                    $stmt->close();
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-heart"></i> Help This Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="help_request">
                    <input type="hidden" name="request_id" id="helpRequestId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <p class="mb-2"><strong>Patient:</strong> <span id="helpPatient"></span></p>
                            <p class="mb-2"><strong>Hospital:</strong> <span id="helpHospital"></span></p>
                            <p class="mb-2"><strong>Blood Type:</strong> 
                               <span class="badge bg-danger" id="helpBloodType"></span></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">How can you help? *</label>
                            <select name="help_type" class="form-select" required>
                                <option value="">Select</option>
                                <option value="interested">I'm interested</option>
                                <option value="can_donate" id="canDonateOption">I can donate</option>
                                <option value="can_coordinate">I can coordinate</option>
                                <option value="maybe">Maybe</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="response_message" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="findCompatibleDonors()">
                                <i class="fas fa-search"></i> Find Compatible Donors
                            </button>
                        </div>
                        
                        <div id="compatibleDonorsList" style="display: none;">
                            <h6>Compatible Donors:</h6>
                            <div id="donorsListContent"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_request_status">
                    <input type="hidden" name="request_id" id="statusRequestId">
                    <div class="modal-body">
                        <label class="form-label">New Status *</label>
                        <select name="new_status" class="form-select" required>
                            <option value="">Select</option>
                            <option value="open">Open</option>
                            <option value="partially_fulfilled">Partially Fulfilled</option>
                            <option value="fulfilled">Fulfilled</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let bloodTypeChart = null;
        let currentRequestBloodType = '';

        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            initializeEventListeners();
            
            // Show active tab if specified
            <?php if ($activeTab && $activeTab != 'requests'): ?>
            showTab('<?php echo $activeTab; ?>');
            <?php endif; ?>
        });

        function loadStats() {
            fetch('?ajax=stats')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('activeRequests').textContent = data.active_requests || 0;
                    document.getElementById('criticalRequests').textContent = data.critical_requests || 0;
                    document.getElementById('availableDonors').textContent = data.available_donors || 0;
                    document.getElementById('totalDonors').textContent = data.total_donors || 0;
                    
                    if (data.blood_types && Object.keys(data.blood_types).length > 0) {
                        createChart(data.blood_types);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function createChart(bloodTypes) {
            const ctx = document.getElementById('bloodTypeChart');
            if (!ctx) return;
            
            if (bloodTypeChart) {
                bloodTypeChart.destroy();
            }
            
            bloodTypeChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(bloodTypes),
                    datasets: [{
                        data: Object.values(bloodTypes),
                        backgroundColor: [
                            '#dc3545', '#28a745', '#ffc107', '#17a2b8',
                            '#6c757d', '#343a40', '#fd7e14', '#e83e8c'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }

        function initializeEventListeners() {
            // Help buttons
            document.querySelectorAll('.help-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const requestId = this.dataset.requestId;
                    const bloodType = this.dataset.bloodType;
                    const patient = this.dataset.patient;
                    const hospital = this.dataset.hospital;
                    const isCompatible = this.dataset.compatible === 'true';
                    
                    currentRequestBloodType = bloodType;
                    document.getElementById('helpRequestId').value = requestId;
                    document.getElementById('helpPatient').textContent = patient;
                    document.getElementById('helpHospital').textContent = hospital;
                    document.getElementById('helpBloodType').textContent = bloodType;
                    
                    const canDonateOption = document.getElementById('canDonateOption');
                    <?php if ($is_donor): ?>
                    canDonateOption.style.display = isCompatible ? 'block' : 'none';
                    <?php else: ?>
                    canDonateOption.style.display = 'none';
                    <?php endif; ?>
                    
                    new bootstrap.Modal(document.getElementById('helpModal')).show();
                });
            });

            // Status update buttons
            document.querySelectorAll('.update-status-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('statusRequestId').value = this.dataset.requestId;
                    new bootstrap.Modal(document.getElementById('statusModal')).show();
                });
            });

            // Donor search form
            const donorSearchForm = document.getElementById('donorSearchForm');
            if (donorSearchForm) {
                donorSearchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    searchDonors();
                });
            }
        }

        function searchDonors() {
            const formData = new FormData(document.getElementById('donorSearchForm'));
            const params = new URLSearchParams();
            params.append('ajax', 'search_donors');
            
            for (let [key, value] of formData.entries()) {
                if (value.trim()) {
                    params.append(key, value.trim());
                }
            }

            showLoading();
            fetch('?' + params.toString())
                .then(response => response.json())
                .then(donors => {
                    displaySearchResults(donors);
                })
                .catch(error => {
                    console.error('Error searching donors:', error);
                    showAlert('Error searching donors', 'danger');
                })
                .finally(() => hideLoading());
        }

        function displaySearchResults(donors) {
            const donorsList = document.getElementById('donorsList');
            
            if (!donors || donors.length === 0) {
                donorsList.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5>No donors found</h5>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                `;
                return;
            }

            let html = '';
            donors.forEach(donor => {
                const isAvailable = donor.availability_status === 'available';
                const statusClass = isAvailable ? 'success' : 'secondary';
                const availabilityText = getAvailabilityText(donor);
                
                html += `
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card donor-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="card-title mb-1">${donor.fname} ${donor.lname}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> ${donor.location}
                                        </small>
                                    </div>
                                    <div class="blood-badge" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                        ${donor.blood_type}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope"></i> ${donor.email}<br>
                                        <i class="fas fa-phone"></i> ${donor.phone}
                                        ${availabilityText}
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-${statusClass}">
                                        ${donor.availability_status.replace('_', ' ').toUpperCase()}
                                    </span>
                                    <button class="btn btn-sm btn-outline-primary contact-donor-btn" 
                                            data-name="${donor.fname} ${donor.lname}"
                                            data-email="${donor.email}"
                                            data-phone="${donor.phone}"
                                            data-location="${donor.location}"
                                            data-blood-type="${donor.blood_type}"
                                            data-status="${donor.availability_status}">
                                        <i class="fas fa-address-book"></i> Contact
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            donorsList.innerHTML = html;
            
            // Re-attach contact event listeners
            document.querySelectorAll('.contact-donor-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    showContactModal(this.dataset);
                });
            });
        }

        function getAvailabilityText(donor) {
            if (donor.availability_status === 'recently_donated' && donor.days_since_donation < 90) {
                const daysLeft = Math.max(0, 90 - donor.days_since_donation);
                return `<br><i class="fas fa-clock text-warning"></i> Available in ${daysLeft} days`;
            }
            return donor.last_donation_date ? `<br><i class="fas fa-calendar"></i> Last donated: ${new Date(donor.last_donation_date).toLocaleDateString()}` : '';
        }

        function loadCompatibleDonors(bloodType, location = '') {
            const params = new URLSearchParams({
                ajax: 'compatible_donors',
                blood_type: bloodType
            });
            
            if (location) {
                params.append('location', location);
            }

            showLoading();
            fetch('?' + params.toString())
                .then(response => response.json())
                .then(donors => {
                    displayCompatibleDonors(donors, bloodType);
                    document.getElementById('donorsModal').dataset.bloodType = bloodType;
                    new bootstrap.Modal(document.getElementById('donorsModal')).show();
                })
                .catch(error => {
                    console.error('Error loading compatible donors:', error);
                    showAlert('Error loading compatible donors', 'danger');
                })
                .finally(() => hideLoading());
        }

        function displayCompatibleDonors(donors, requestedBloodType) {
            const donorsList = document.getElementById('donorsList');
            
            if (!donors || donors.length === 0) {
                donorsList.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5>No compatible donors found</h5>
                        <p class="text-muted">Try expanding the location search or check back later</p>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="alert alert-info mb-3">
                    <strong>Found ${donors.length} compatible donor(s) for ${requestedBloodType}</strong>
                </div>
                <div class="row">
            `;
            
            donors.forEach(donor => {
                const isAvailable = donor.availability_status === 'available';
                const statusClass = isAvailable ? 'success' : 'secondary';
                const availabilityText = getAvailabilityText(donor);

                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card donor-card">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="blood-badge" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                            ${donor.blood_type}
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="mb-1">${donor.fname} ${donor.lname}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> ${donor.location}<br>
                                            <i class="fas fa-phone"></i> ${donor.phone}
                                            ${availabilityText}
                                        </small>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-${statusClass} mb-2">${donor.availability_status.replace('_', ' ')}</span><br>
                                        <button class="btn btn-sm btn-outline-primary contact-donor-btn" 
                                                data-name="${donor.fname} ${donor.lname}"
                                                data-email="${donor.email}"
                                                data-phone="${donor.phone}"
                                                data-location="${donor.location}"
                                                data-blood-type="${donor.blood_type}"
                                                data-status="${donor.availability_status}">
                                            <i class="fas fa-phone"></i> Contact
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            donorsList.innerHTML = html;
            
            // Re-attach contact event listeners
            document.querySelectorAll('.contact-donor-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    showContactModal(this.dataset);
                });
            });
        }

        function showContactModal(donorData) {
            document.getElementById('contactName').textContent = donorData.name;
            document.getElementById('contactLocation').textContent = donorData.location;
            document.getElementById('contactBloodType').textContent = donorData.bloodType;
            document.getElementById('contactStatus').innerHTML = `<span class="badge bg-${donorData.status === 'available' ? 'success' : 'secondary'}">${donorData.status.replace('_', ' ')}</span>`;
            document.getElementById('contactEmail').href = 'mailto:' + donorData.email;
            document.getElementById('contactEmail').innerHTML = `<i class="fas fa-envelope"></i> ${donorData.email}`;
            document.getElementById('contactPhone').href = 'tel:' + donorData.phone;
            document.getElementById('contactPhone').innerHTML = `<i class="fas fa-phone"></i> ${donorData.phone}`;
            
            new bootstrap.Modal(document.getElementById('contactModal')).show();
        }

        function showCompatibleTypes(bloodType) {
            const preview = document.getElementById('compatiblePreview');
            const typesDiv = document.getElementById('compatibleTypes');
            
            if (bloodType && bloodCompatibility[bloodType]) {
                const types = bloodCompatibility[bloodType];
                typesDiv.innerHTML = types.map(type => 
                    `<span class="badge bg-primary me-1">${type}</span>`
                ).join('');
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        function showTab(tabId) {
            const tab = document.querySelector(`[data-bs-target="#${tabId}"]`);
            if (tab) {
                new bootstrap.Tab(tab).show();
            }
        }

        function showLoading() {
            document.getElementById('loadingSpinner').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').classList.remove('active');
        }

        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'danger' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.querySelector('.container-fluid');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
                setTimeout(dismissAlerts, 5000);
            }
        }

        function dismissAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
                try {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } catch (e) {
                    // Alert might already be closed
                }
            });
        }

        // Form validation functions
        function validateDonorForm() {
            const age = parseInt(document.querySelector('input[name="age"]').value);
            const weight = parseFloat(document.querySelector('input[name="weight"]').value);
            
            if (age < 18 || age > 65) {
                showAlert('Age must be between 18-65 years', 'danger');
                return false;
            }
            
            if (weight < 50) {
                showAlert('Minimum weight requirement is 50kg', 'danger');
                return false;
            }
            
            return true;
        }

        function validateRequestForm() {
            const neededDate = new Date(document.querySelector('input[name="needed_by_date"]').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (neededDate < today) {
                showAlert('Needed by date cannot be in the past', 'danger');
                return false;
            }
            
            return true;
        }

        // Auto-refresh stats every 5 minutes
        setInterval(loadStats, 300000);
    </script>
</body>
</html>
<?php $conn->close(); ?>