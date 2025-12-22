<?php
session_start();
header('Content-Type: application/json');
include "supabase_alt.php";

$response = ["success" => false, "message" => "Registration failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstname'] ?? $_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastname'] ?? $_POST['lastName'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? $_POST['confirmPassword'] ?? '';

    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $response['message'] = "All fields are required";
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format";
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirmPassword) {
        $response['message'] = "Passwords do not match";
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < 6) {
        $response['message'] = "Password must be at least 6 characters";
        echo json_encode($response);
        exit;
    }

    try {
        // Check if email exists
        $existing = supabaseQueryAlt('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (!empty($existing)) {
            $response['message'] = "Email already registered";
            echo json_encode($response);
            exit;
        }

        // Create user
        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'passenger',
            'created_at' => date('Y-m-d\TH:i:s\Z')
        ];

        $result = supabaseQueryAlt('users', 'POST', $userData);
        
        if ($result) {
            // Set session
            $_SESSION['user_id'] = $result[0]['id'];
            $_SESSION['name'] = $firstName . ' ' . $lastName;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'passenger';
            $_SESSION['logged_in'] = true;
            
            $response = [
                "success" => true,
                "message" => "Registration successful! Welcome to Biyahe Now!",
                "redirect" => "Php/Passenger-dashboard.php"
            ];
        } else {
            $response['message'] = "Registration failed. Please try again.";
        }
        
    } catch (Exception $e) {
        $response['message'] = "Registration error: " . $e->getMessage();
    }
}

echo json_encode($response);
?>