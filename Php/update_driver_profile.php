<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

try {
    include "db.php";
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$response = ['success' => false, 'message' => ''];

// 1. Tiyakin na naka-log in bilang driver
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'driver') {
    $response['message'] = 'Access denied or invalid session.';
    echo json_encode($response);
    exit;
}

$accountId = $_SESSION['account_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Kuhanin ang data mula sa form submission
    $address            = trim($_POST['address'] ?? '');
    $contact            = trim($_POST['contact'] ?? '');
    $dob                = trim($_POST['dob'] ?? '');
    $gender             = trim($_POST['gender'] ?? '');
    $vehicle_type       = trim($_POST['vehicle_type'] ?? '');
    $plate_number       = trim($_POST['plate_number'] ?? '');
    $license_number     = trim($_POST['license_number'] ?? '');
    $license_expiry     = trim($_POST['license_expiry'] ?? '');
    $area_of_operation  = trim($_POST['area_of_operation'] ?? '');
    $working_schedule   = trim($_POST['working_schedule'] ?? '');
    $experience_years   = trim($_POST['experience_years'] ?? '');
    
    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = 'driver_' . $accountId . '_' . time() . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $uploadPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
            $profile_picture = 'uploads/profiles/' . $fileName;
        }
    }

    // Build UPDATE query
    if ($profile_picture) {
        $sql_update = "UPDATE drivers SET 
                            address = ?, contact = ?, dob = ?, gender = ?, vehicle_type = ?, 
                            plate_number = ?, license_number = ?, license_expiry = ?, 
                            area_of_operation = ?, working_schedule = ?, experience_years = ?, profile_picture = ?
                        WHERE account_id = ?";
    } else {
        $sql_update = "UPDATE drivers SET 
                            address = ?, contact = ?, dob = ?, gender = ?, vehicle_type = ?, 
                            plate_number = ?, license_number = ?, license_expiry = ?, 
                            area_of_operation = ?, working_schedule = ?, experience_years = ?
                        WHERE account_id = ?";
    }
    
    if ($stmt = $conn->prepare($sql_update)) {
        if ($profile_picture) {
            $stmt->bind_param("ssssssssssssi", 
                $address, $contact, $dob, $gender, $vehicle_type, $plate_number, 
                $license_number, $license_expiry, $area_of_operation, $working_schedule, 
                $experience_years, $profile_picture, $accountId
            );
        } else {
            $stmt->bind_param("sssssssssssi", 
                $address, $contact, $dob, $gender, $vehicle_type, $plate_number, 
                $license_number, $license_expiry, $area_of_operation, $working_schedule, 
                $experience_years, $accountId
            );
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Profile updated successfully!'];
            } else {
                // Walang error, pero walang binago (walang bagong data)
                $response = ['success' => true, 'message' => 'No changes were made to the profile.'];
            }
        } else {
            $response['message'] = 'Execute error (UPDATE drivers): ' . $stmt->error;
        }
        $stmt->close();

    } else {
        $response['message'] = 'Database error: ' . $conn->error;
    }

} else {
    $response['message'] = 'Invalid request method.';
}

if (isset($conn)) {
    $conn->close();
}

echo json_encode($response);
?>