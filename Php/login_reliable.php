<?php
session_start();
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Login failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = "Email and password are required";
        echo json_encode($response);
        exit;
    }

    // File-based admin accounts
    $admin_accounts = [
        'mariaednalynalbos@gmail.com' => [
            'password' => 'password123456789',
            'name' => 'Maria Ednalyn Albos',
            'role' => 'admin'
        ],
        'admin@biyahe.com' => [
            'password' => 'admin123',
            'name' => 'System Admin',
            'role' => 'admin'
        ]
    ];

    // Try Supabase first, then fallback to file-based
    try {
        include "supabase_alt.php";
        $users = supabaseQueryAlt('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (!empty($users)) {
            $user = $users[0];
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['user_type'] ?? 'passenger';
                $_SESSION['logged_in'] = true;

                $redirect = 'Php/Passenger-dashboard.php';
                if ($user['user_type'] === 'admin') {
                    $redirect = 'Php/Admin-dashboard.php';
                } elseif ($user['user_type'] === 'driver') {
                    $redirect = 'dashboard.html';
                }

                $response = [
                    "success" => true,
                    "message" => "Login successful! (Database)",
                    "redirect" => $redirect,
                    "role" => $user['user_type']
                ];
                echo json_encode($response);
                exit;
            }
        }
    } catch (Exception $e) {
        // Supabase failed, continue to file-based check
    }

    // File-based authentication
    if (isset($admin_accounts[$email])) {
        $account = $admin_accounts[$email];
        
        if ($password === $account['password']) {
            $_SESSION['user_id'] = 1;
            $_SESSION['name'] = $account['name'];
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $account['role'];
            $_SESSION['logged_in'] = true;

            $redirect = $account['role'] === 'admin' ? 'Php/Admin-dashboard.php' : 
                       ($account['role'] === 'driver' ? 'dashboard.html' : 'Php/Passenger-dashboard.php');

            $response = [
                "success" => true,
                "message" => "Login successful! (File-based)",
                "redirect" => $redirect,
                "role" => $account['role']
            ];
        } else {
            $response['message'] = "Invalid password";
        }
    } else {
        $response['message'] = "Account not found. Available admin: mariaednalynalbos@gmail.com";
    }
}

echo json_encode($response);
?>