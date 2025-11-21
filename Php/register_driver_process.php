<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Simple database connection
$conn = new mysqli("localhost", "root", "", "biyahe_now");

if ($conn->connect_error) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lastname = trim($_POST['lastName'] ?? '');
    $firstname = trim($_POST['firstName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = 'driver';
    
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contactNumber'] ?? '');
    $vehicle_type = trim($_POST['vehicleType'] ?? '');
    $plate_number = trim($_POST['plateNumber'] ?? '');
    $license_number = trim($_POST['licenseNumber'] ?? '');

    if (empty($email) || empty($password) || empty($lastname) || empty($firstname) || empty($license_number)) {
        $response['message'] = 'All required fields must be filled';
        echo json_encode($response);
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT account_id FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = 'Email already registered';
        echo json_encode($response);
        exit;
    }

    // Insert into accounts
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $account_id = $conn->insert_id;
        
        // Insert into drivers table
        $stmt2 = $conn->prepare("INSERT INTO drivers (account_id, firstname, lastname, address, contact, vehicle_type, plate_number, license_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isssssss", $account_id, $firstname, $lastname, $address, $contact, $vehicle_type, $plate_number, $license_number);
        
        if ($stmt2->execute()) {
            $response['success'] = true;
            $response['message'] = 'Driver registered successfully!';
        } else {
            $conn->query("DELETE FROM accounts WHERE account_id = $account_id");
            $response['message'] = 'Failed to create driver profile';
        }
    } else {
        $response['message'] = 'Failed to create account';
    }
}

$conn->close();
echo json_encode($response);
?>