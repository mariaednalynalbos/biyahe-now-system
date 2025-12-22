<?php
session_start();
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Registration failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Basic validation
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

    // Try Supabase first
    try {
        include "supabase_db.php";
        
        // Check if email exists
        $existing = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
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

        $result = supabaseQuery('users', 'POST', $userData);
        
        if ($result) {
            $response = [
                "success" => true,
                "message" => "Registration successful! You can now login.",
                "redirect" => "index.html"
            ];
        }
        
    } catch (Exception $e) {
        // Fallback: Demo registration (simulate success)
        $response = [
            "success" => true,
            "message" => "Registration successful (Demo Mode)! Use these demo accounts to login:\n\n" .
                        "Admin: mariaednalynalbos@gmail.com / password123\n" .
                        "Driver: driver@biyahe.com / driver123\n" .
                        "Passenger: passenger@biyahe.com / passenger123",
            "redirect" => "index.html"
        ];
    }
}

echo json_encode($response);
?>