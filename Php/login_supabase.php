<?php
session_start();
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Unexpected error occurred."];

try {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception("Please enter both email and password.");
    }

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    
    // Try Supabase first
    try {
        include "supabase_db.php";
        $users = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (!empty($users)) {
            $user = $users[0];
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['user_type'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['logged_in'] = true;

                $redirect = $user['user_type'] === 'driver' ? "dashboard.html" : 
                           ($user['user_type'] === 'passenger' ? "Php/Passenger-dashboard.php" : "Php/Admin-dashboard.php");

                $response = [
                    "status" => "success",
                    "message" => "Login successful!",
                    "role" => $user['user_type'],
                    "redirect" => $redirect
                ];
                echo json_encode($response);
                exit;
            }
        }
    } catch (Exception $db_error) {
        // Supabase failed, use demo accounts
        $demo_accounts = [
            'admin@biyahe.com' => ['password' => 'admin123', 'name' => 'Admin User', 'role' => 'admin'],
            'mariaednalynalbos@gmail.com' => ['password' => 'password123', 'name' => 'Maria Ednalyn Albos', 'role' => 'admin'],
            'driver@biyahe.com' => ['password' => 'driver123', 'name' => 'Test Driver', 'role' => 'driver'],
            'passenger@biyahe.com' => ['password' => 'passenger123', 'name' => 'Test Passenger', 'role' => 'passenger']
        ];

        if (isset($demo_accounts[$email]) && $password === $demo_accounts[$email]['password']) {
            $_SESSION['user_id'] = 1;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $demo_accounts[$email]['role'];
            $_SESSION['name'] = $demo_accounts[$email]['name'];
            $_SESSION['logged_in'] = true;

            $redirect = $demo_accounts[$email]['role'] === 'driver' ? "dashboard.html" : 
                       ($demo_accounts[$email]['role'] === 'passenger' ? "Php/Passenger-dashboard.php" : "Php/Admin-dashboard.php");

            $response = [
                "status" => "success",
                "message" => "Login successful (Demo Mode)!",
                "role" => $demo_accounts[$email]['role'],
                "redirect" => $redirect
            ];
            echo json_encode($response);
            exit;
        }
    }
    
    throw new Exception("Invalid email or password.");

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>