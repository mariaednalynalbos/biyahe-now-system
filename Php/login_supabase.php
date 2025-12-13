<?php
session_start();
header('Content-Type: application/json');
include "supabase_db.php";

$response = ["status" => "error", "message" => "Unexpected error occurred."];

try {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception("Please enter both email and password.");
    }

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    
    // GET USER
    $users = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
    
    // Debug logging
    error_log("Login attempt for email: " . $email);
    error_log("Users found: " . count($users));
    
    if (empty($users)) {
        throw new Exception("Email not found.");
    }

    $user = $users[0];

    // CHECK PASSWORD
    if (!password_verify($password, $user['password'])) {
        throw new Exception("Incorrect password.");
    }

    // SET SESSION
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['user_type'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['logged_in'] = true;

    // REDIRECT
    if ($user['user_type'] === 'driver') {
        $redirect = "dashboard.html";
    } elseif ($user['user_type'] === 'passenger') {
        $redirect = "Php/Passenger-dashboard.php";
    } else {
        $redirect = "Php/Admin-dashboard.php";
    }

    $response = [
        "status" => "success",
        "message" => "Login successful!",
        "role" => $user['user_type'],
        "redirect" => $redirect
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>