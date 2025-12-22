<?php
session_start();
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Registration failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstname'] ?? $_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastname'] ?? $_POST['lastName'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? $_POST['confirmPassword'] ?? '';

    // Validation
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

    // Create users.json file if it doesn't exist
    $usersFile = __DIR__ . '/users.json';
    if (!file_exists($usersFile)) {
        file_put_contents($usersFile, json_encode([]));
    }

    // Read existing users
    $users = json_decode(file_get_contents($usersFile), true) ?: [];

    // Check if email already exists
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $response['message'] = "Email already registered";
            echo json_encode($response);
            exit;
        }
    }

    // Create new user
    $newUser = [
        'id' => count($users) + 1,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'name' => $firstName . ' ' . $lastName,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'user_type' => 'passenger',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Add to users array
    $users[] = $newUser;

    // Save to file
    if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT))) {
        // Set session
        $_SESSION['user_id'] = $newUser['id'];
        $_SESSION['name'] = $newUser['name'];
        $_SESSION['email'] = $newUser['email'];
        $_SESSION['role'] = $newUser['user_type'];
        $_SESSION['logged_in'] = true;
        
        $response = [
            "success" => true,
            "message" => "Registration successful! Welcome to Biyahe Now!",
            "redirect" => "Php/Passenger-dashboard.php"
        ];
    } else {
        $response['message'] = "Failed to save user data";
    }
}

echo json_encode($response);
?>