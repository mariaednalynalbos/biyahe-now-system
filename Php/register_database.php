<?php
session_start();
header('Content-Type: application/json');

require_once 'db_persistent.php';

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

    if (!$pdo) {
        $response['message'] = "Database connection failed";
        echo json_encode($response);
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $response['message'] = "Email already registered";
            echo json_encode($response);
            exit;
        }

        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, name, email, password, user_type) 
            VALUES (?, ?, ?, ?, ?, 'passenger')
        ");
        
        $fullName = $firstName . ' ' . $lastName;
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if ($stmt->execute([$firstName, $lastName, $fullName, $email, $hashedPassword])) {
            // Get the new user ID
            $userId = $pdo->lastInsertId();
            
            // Set session
            $_SESSION['user_id'] = $userId;
            $_SESSION['name'] = $fullName;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'passenger';
            $_SESSION['logged_in'] = true;
            
            $response = [
                "success" => true,
                "message" => "Registration successful! Welcome to Biyahe Now!",
                "redirect" => "Php/Passenger-dashboard.php"
            ];
        } else {
            $response['message'] = "Failed to create account";
        }
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        $response['message'] = "Database error occurred";
    }
}

echo json_encode($response);
?>