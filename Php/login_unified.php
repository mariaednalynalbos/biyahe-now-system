<?php
session_start();
header('Content-Type: application/json');

// Debug logging
error_log("Login attempt started");

$response = ["success" => false, "message" => "Login failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    error_log("Login attempt for email: $email");

    if (empty($email) || empty($password)) {
        $response['message'] = "Email and password are required";
        echo json_encode($response);
        exit;
    }

    // Create users.json file if it doesn't exist
    $usersFile = __DIR__ . '/users.json';
    if (!file_exists($usersFile)) {
        file_put_contents($usersFile, json_encode([]));
    }

    // Read users from file
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    
    // Debug: Log users file content
    error_log("Users file content: " . json_encode($users));
    error_log("Looking for email: $email");

    // Find user
    $user = null;
    foreach ($users as $u) {
        error_log("Checking user: " . json_encode($u));
        if ($u['email'] === $email) {
            $user = $u;
            error_log("User found: " . json_encode($user));
            break;
        }
    }

    if (!$user) {
        // Check hardcoded admin accounts
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
            ],
            'passenger@test.com' => [
                'password' => 'passenger123',
                'name' => 'Test Passenger',
                'role' => 'passenger'
            ]
        ];

        if (isset($admin_accounts[$email])) {
            error_log("Hardcoded account found for: $email");
            $account = $admin_accounts[$email];
            if ($password === $account['password']) {
                error_log("Password verified for: $email");
                $_SESSION['user_id'] = ($account['role'] === 'admin') ? 1 : 2;
                $_SESSION['name'] = $account['name'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $account['role'];
                $_SESSION['logged_in'] = true;

                $redirect = ($account['role'] === 'admin') ? 'Php/Admin-dashboard.php' : 'Php/Passenger-dashboard.php';
                
                $response = [
                    "success" => true,
                    "message" => "Login successful!",
                    "redirect" => $redirect,
                    "role" => $account['role']
                ];
            } else {
                error_log("Password mismatch for: $email");
                $response['message'] = "Invalid password";
            }
        } else {
            error_log("Account not found for: $email");
            $response['message'] = "Account not found";
        }
    } else {
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['user_type'];
            $_SESSION['logged_in'] = true;

            $redirect = 'Php/Passenger-dashboard.php';
            if ($user['user_type'] === 'admin') {
                $redirect = 'Php/Admin-dashboard.php';
            } elseif ($user['user_type'] === 'driver') {
                $redirect = 'dashboard.html';
            }

                $response = [
                    "success" => true,
                    "message" => "Login successful!",
                    "redirect" => $redirect,
                    "role" => $user['user_type']
                ];
        } else {
            $response['message'] = "Invalid password";
        }
    }
}

echo json_encode($response);
?>