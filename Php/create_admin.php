<?php
header('Content-Type: application/json');
include "supabase_alt.php";

$response = ["success" => false, "message" => "Admin setup failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($password)) {
        $response['message'] = "Password is required";
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
        $email = 'mariaednalynalbos@gmail.com';
        
        // Check if admin already exists
        $existing = supabaseQueryAlt('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (!empty($existing)) {
            $response['message'] = "Admin account already exists! You can login now.";
            echo json_encode($response);
            exit;
        }

        // Create admin account
        $userData = [
            'first_name' => 'Maria Ednalyn',
            'last_name' => 'Albos',
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'admin',
            'created_at' => date('Y-m-d\TH:i:s\Z')
        ];

        $result = supabaseQueryAlt('users', 'POST', $userData);
        
        if ($result) {
            $response = [
                "success" => true,
                "message" => "Admin account created successfully! You can now login with: " . $email,
                "email" => $email
            ];
        } else {
            $response['message'] = "Failed to create admin account";
        }
        
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
}

echo json_encode($response);
?>