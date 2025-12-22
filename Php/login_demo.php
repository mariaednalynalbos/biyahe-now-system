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

    // Hardcoded admin accounts for demo
    $demo_accounts = [
        'admin@biyahe.com' => [
            'password' => 'admin123',
            'name' => 'Admin User',
            'role' => 'admin'
        ],
        'mariaednalynalbos@gmail.com' => [
            'password' => 'password123',
            'name' => 'Maria Ednalyn Albos',
            'role' => 'admin'
        ],
        'driver@biyahe.com' => [
            'password' => 'driver123',
            'name' => 'Test Driver',
            'role' => 'driver'
        ],
        'passenger@biyahe.com' => [
            'password' => 'passenger123',
            'name' => 'Test Passenger',
            'role' => 'passenger'
        ]
    ];

    if (isset($demo_accounts[$email])) {
        $account = $demo_accounts[$email];
        
        if ($password === $account['password']) {
            $_SESSION['user_id'] = 1;
            $_SESSION['name'] = $account['name'];
            $_SESSION['role'] = $account['role'];
            
            $redirect = $account['role'] === 'admin' ? "Php/Admin-dashboard.php" : 
                       ($account['role'] === 'driver' ? "dashboard.html" : "Php/Passenger-dashboard.php");
            
            $response = [
                "success" => true,
                "message" => "Login successful (Demo Mode)",
                "redirect" => $redirect
            ];
        } else {
            $response['message'] = "Invalid password";
        }
    } else {
        $response['message'] = "Account not found. Use demo accounts: admin@biyahe.com (admin123) or mariaednalynalbos@gmail.com (password123)";
    }
}

echo json_encode($response);
?>