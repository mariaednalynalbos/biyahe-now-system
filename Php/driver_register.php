<?php
session_start();
header('Content-Type: application/json');
ob_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    ob_clean();
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "All fields required"]);
        exit;
    }

    $usersFile = __DIR__ . '/users.json';
    if (!file_exists($usersFile)) {
        file_put_contents($usersFile, '[]');
    }

    $users = json_decode(file_get_contents($usersFile), true) ?: [];

    foreach ($users as $user) {
        if ($user['email'] === $email) {
            ob_clean();
            echo json_encode(["success" => false, "message" => "Email already exists"]);
            exit;
        }
    }

    $users[] = [
        'id' => count($users) + 1,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'name' => $firstName . ' ' . $lastName,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'user_type' => 'driver',
        'created_at' => date('Y-m-d H:i:s')
    ];

    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    
    ob_clean();
    echo json_encode(["success" => true, "message" => "Driver registered successfully!"]);
    exit;
}

ob_clean();
echo json_encode(["success" => false, "message" => "Invalid request"]);
?>