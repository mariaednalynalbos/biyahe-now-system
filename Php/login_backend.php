<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

include "db.php";

$response = ["status" => "error", "message" => "Unexpected error occurred."];

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception("Please enter both email and password.");
    }

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    
    // Debug: Log login attempt
    error_log("Login attempt for email: $email");
   
    // Check users table
    $stmt = $conn->prepare(
        "SELECT id, name, email, password, user_type 
         FROM users 
         WHERE email = ?"
    );

    $stmt->execute([$email]);
    $account = $stmt->fetch();

    if (!$account) {
        throw new Exception("Email not found.");
    }

    // Check password - handle both hashed and plain text (for debugging)
    $passwordValid = false;
    
    if (password_verify($password, $account['password'])) {
        $passwordValid = true;
    } elseif ($password === $account['password']) {
        // Temporary: Allow plain text passwords (will be fixed by fix script)
        $passwordValid = true;
        error_log("WARNING: Plain text password detected for $email");
    }
    
    if (!$passwordValid) {
        error_log("Password verification failed for $email");
        throw new Exception("Incorrect password.");
    }

    // Save basic session data
    $_SESSION['user_id'] = $account['id'];
    $_SESSION['email'] = $account['email'];
    $_SESSION['role'] = $account['user_type'];
    $_SESSION['logged_in'] = true;
    $_SESSION['name'] = $account['name'] ?? '';

    // Redirect based on role
    if ($account['user_type'] === 'driver') {
        $redirect = "dashboard.html";
    } elseif ($account['user_type'] === 'passenger') {
        $redirect = "passenger-dashboard.html";
    } else {
        $redirect = "admin-dashboard.html";
    }

    $response = [
        "status" => "success",
        "message" => "Login successful!",
        "role" => $account['user_type'],
        "redirect" => $redirect
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
