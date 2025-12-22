<?php
session_start();
header('Content-Type: application/json');
include "supabase_alt.php";

$response = ["success" => false, "message" => "Login failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = "Email and password are required";
        echo json_encode($response);
        exit;
    }

    try {
        // Get user by email
        $users = supabaseQueryAlt('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (empty($users)) {
            $response['message'] = "Email not found";
            echo json_encode($response);
            exit;
        }

        $user = $users[0];

        // Verify password
        if (!password_verify($password, $user['password'])) {
            $response['message'] = "Invalid password";
            echo json_encode($response);
            exit;
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['user_type'] ?? 'passenger';
        $_SESSION['logged_in'] = true;

        // Determine redirect
        $redirect = 'Php/Passenger-dashboard.php';
        if ($user['user_type'] === 'admin') {
            $redirect = 'Php/Admin-dashboard.php';
        } elseif ($user['user_type'] === 'driver') {
            $redirect = 'dashboard.html';
        }

        $response = [
            "success" => true,
            "message" => "Login successful! Welcome back!",
            "redirect" => $redirect,
            "role" => $user['user_type']
        ];
        
    } catch (Exception $e) {
        $response['message'] = "Login error: " . $e->getMessage();
    }
}

echo json_encode($response);
?>