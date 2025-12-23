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

    // Check hardcoded admin accounts first
    $admin_accounts = [
        'mariaednalynalbos@gmail.com' => [
            'password' => 'password123456789',
            'name' => 'Maria Ednalyn Albos',
            'role' => 'admin'
        ]
    ];

    if (isset($admin_accounts[$email])) {
        $account = $admin_accounts[$email];
        if ($password === $account['password']) {
            $_SESSION['user_id'] = 1;
            $_SESSION['name'] = $account['name'];
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $account['role'];
            $_SESSION['logged_in'] = true;

            $response = [
                "success" => true,
                "message" => "Login successful!",
                "redirect" => "Php/Admin-dashboard.php",
                "role" => $account['role']
            ];
            echo json_encode($response);
            exit;
        }
    }

    // Try database first
    try {
        require_once 'db_persistent.php';
        
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT id, name, email, password, user_type FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['user_type'];
                $_SESSION['logged_in'] = true;

                $redirect = 'Php/Passenger-dashboard.php';
                if ($user['user_type'] === 'admin') {
                    $redirect = 'Php/Admin-dashboard.php';
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
        error_log("Database login failed: " . $e->getMessage());
    }

    // Fallback to file system
    $usersFile = __DIR__ . '/users.json';
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?: [];

        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['user_type'];
                $_SESSION['logged_in'] = true;

                $redirect = 'Php/Passenger-dashboard.php';
                if ($user['user_type'] === 'admin') {
                    $redirect = 'Php/Admin-dashboard.php';
                }

                $response = [
                    "success" => true,
                    "message" => "Login successful! (File)",
                    "redirect" => $redirect,
                    "role" => $user['user_type']
                ];
                echo json_encode($response);
                exit;
            }
        }
    }

    $response['message'] = "Invalid email or password";
}

echo json_encode($response);
?>