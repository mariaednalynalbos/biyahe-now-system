<?php
session_start();
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Driver registration failed"];

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = "Access denied. Admin only.";
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName'] ?? $_POST['firstname'] ?? '');
    $lastName = trim($_POST['lastName'] ?? $_POST['lastname'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $licenseNumber = trim($_POST['licenseNumber'] ?? $_POST['license_number'] ?? '');
    $phoneNumber = trim($_POST['phoneNumber'] ?? $_POST['phone_number'] ?? '');

    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $response['message'] = "All required fields must be filled";
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

    // Try hybrid system (database first, then file fallback)
    try {
        require_once 'db_persistent.php';
        
        if ($pdo) {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $response['message'] = "Email already registered";
                echo json_encode($response);
                exit;
            }

            // Insert new driver
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, name, email, password, user_type) 
                VALUES (?, ?, ?, ?, ?, 'driver')
            ");
            
            $fullName = $firstName . ' ' . $lastName;
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$firstName, $lastName, $fullName, $email, $hashedPassword])) {
                $response = [
                    "success" => true,
                    "message" => "Driver registered successfully!",
                    "driver" => [
                        "name" => $fullName,
                        "email" => $email,
                        "license" => $licenseNumber
                    ]
                ];
                echo json_encode($response);
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("Database driver registration failed: " . $e->getMessage());
    }

    // Fallback to file system
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
        'license_number' => $licenseNumber,
        'phone_number' => $phoneNumber,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $users[] = $newDriver;

    if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT))) {
        $response = [
            "success" => true,
            "message" => "Driver registered successfully!",
            "driver" => [
                "name" => $newDriver['name'],
                "email" => $newDriver['email'],
                "license" => $licenseNumber
            ]
        ];
    } else {
        $response['message'] = "Failed to save driver data";
    }
}

echo json_encode($response);
?>