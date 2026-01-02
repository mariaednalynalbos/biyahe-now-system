<?php
session_start();
header('Content-Type: application/json');

// Prevent any HTML output
ob_start();

$response = ["success" => false, "message" => "Driver registration failed"];

try {
    // Check if admin is logged in
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $response['message'] = "Access denied. Admin only.";
        echo json_encode($response);
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Basic validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            $response['message'] = "First name, last name, email, and password are required";
            echo json_encode($response);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = "Invalid email format";
            echo json_encode($response);
            exit;
        }

        if (strlen($password) < 6) {
            $response['message'] = "Password must be at least 6 characters";
            echo json_encode($response);
            exit;
        }

        if ($password !== $confirmPassword) {
            $response['message'] = "Passwords do not match";
            echo json_encode($response);
            exit;
        }

        // Use file system (simple and reliable)
        $usersFile = __DIR__ . '/users.json';
        if (!file_exists($usersFile)) {
            file_put_contents($usersFile, json_encode([]));
        }

        $users = json_decode(file_get_contents($usersFile), true) ?: [];

        // Check if email already exists
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $response['message'] = "Email already registered";
                echo json_encode($response);
                exit;
            }
        }

        // Create new driver
        $newDriver = [
            'id' => count($users) + 1,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'driver',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $users[] = $newDriver;

        if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT))) {
            $response = [
                "success" => true,
                "message" => "Driver registered successfully!",
                "driver" => [
                    "name" => $newDriver['name'],
                    "email" => $newDriver['email']
                ]
            ];
        } else {
            $response['message'] = "Failed to save driver data";
        }
    }

} catch (Exception $e) {
    $response['message'] = "System error: " . $e->getMessage();
}

// Clear any unwanted output
ob_clean();
echo json_encode($response);
?>