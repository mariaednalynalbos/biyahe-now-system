<?php
header('Content-Type: application/json');
session_start();
include "supabase_db.php";

$response = ["success" => false, "message" => "Unknown error occurred."];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $lastname = trim($_POST['lastName'] ?? '');
    $firstname = trim($_POST['firstName'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirmPassword'] ?? '';
    $contact = trim($_POST['contactNumber'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob = $_POST['dateOfBirth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $vehicleType = trim($_POST['vehicleType'] ?? '');
    $plateNumber = trim($_POST['plateNumber'] ?? '');
    $licenseNumber = trim($_POST['licenseNumber'] ?? '');
    $licenseExpiry = $_POST['licenseExpiryDate'] ?? '';
    $areaOfOperation = trim($_POST['areaOfOperation'] ?? '');
    $workingSchedule = trim($_POST['workingSchedule'] ?? '');
    $yearsExperience = intval($_POST['yearsExperience'] ?? 0);

    // Basic validation
    $errors = [];
    if (empty($lastname) || empty($firstname)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";
    if (empty($contact)) $errors[] = "Contact number is required.";
    if (empty($licenseNumber)) $errors[] = "License number is required.";
    if (empty($plateNumber)) $errors[] = "Plate number is required.";

    if (!empty($errors)) {
        $response['message'] = implode(" ", $errors);
        echo json_encode($response);
        exit;
    }

    try {
        // Check if email exists
        $existing = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (!empty($existing)) {
            $response['message'] = "Email already registered!";
            echo json_encode($response);
            exit;
        }

        // Create user account
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $full_name = $firstname . ' ' . $lastname;

        $userData = [
            'name' => $full_name,
            'email' => $email,
            'password' => $hashed,
            'user_type' => 'driver',
            'contact' => $contact,
            'address' => $address,
            'date_of_birth' => $dob,
            'gender' => $gender,
            'vehicle_type' => $vehicleType,
            'plate_number' => $plateNumber,
            'license_number' => $licenseNumber,
            'license_expiry' => $licenseExpiry,
            'area_of_operation' => $areaOfOperation,
            'working_schedule' => $workingSchedule,
            'years_experience' => $yearsExperience,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = supabaseQuery('users', 'POST', $userData);

        if (!empty($result)) {
            $response = [
                "success" => true,
                "message" => "Driver registered successfully!",
                "redirect" => "dashboard.html"
            ];
        } else {
            $response['message'] = "Registration failed. Please try again.";
        }

    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }

} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>