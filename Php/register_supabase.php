<?php
header('Content-Type: application/json');
session_start();
include "supabase_db.php";

$response = ["status" => "error", "message" => "Unknown error occurred."];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $lastname = trim($_POST['lastname'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $email  = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'passenger');

    // VALIDATION
    $errors = [];

    if ($lastname === '' || $firstname === '') $errors[] = "Please enter both surname and first name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";
    if (!in_array($role, ['admin','driver','passenger'])) $role = 'passenger';

    if (!empty($errors)) {
        echo json_encode(["status" => "error", "message" => implode("\n", $errors)]);
        exit;
    }

    try {
        // CHECK EMAIL
        $existing = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (!empty($existing)) {
            echo json_encode(["status" => "error", "message" => "Email already registered!"]);
            exit;
        }

        // INSERT USER
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $full_name = $firstname . ' ' . $lastname;

        $userData = [
            'name' => $full_name,
            'email' => $email,
            'password' => $hashed,
            'user_type' => $role,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = supabaseQuery('users', 'POST', $userData);

        if (empty($result)) {
            throw new Exception("Registration failed");
        }

        $new_user_id = $result[0]['id'];

        // SET SESSION
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['name'] = $full_name;
        $_SESSION['logged_in'] = true;

        // REDIRECT
        if ($role === 'driver') {
            $redirect = "dashboard.html";
        } elseif ($role === 'passenger') {
            $redirect = "Php/Passenger-dashboard.php";
        } else {
            $redirect = "Php/Admin-dashboard.php";
        }

        echo json_encode([
            "status" => "success",
            "message" => "Registration successful!",
            "role" => $role,
            "redirect" => $redirect
        ]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Direct access not allowed."]);
}
?>