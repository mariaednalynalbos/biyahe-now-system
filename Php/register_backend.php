<?php
header('Content-Type: application/json');
session_start();
include "db.php";

$response = ["status" => "error", "message" => "Unknown error occurred."];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $lastname = trim($_POST['lastname'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $email  = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'passenger');

    // VALIDATION
    $errors = [];

    if ($lastname === '' || $firstname === '') $errors[] = "Please enter both surname and first name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";
    if (!in_array($role, ['admin','driver','passenger'])) $role = 'passenger';

    if (!empty($errors)) {
        echo json_encode(["status" => "error", "message" => implode("\n", $errors)]);
        exit;
    }

    // CHECK EMAIL
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered!"]);
        exit;
    }

    // INSERT INTO USERS
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $full_name = $firstname . ' ' . $lastname;

    $stmt_acc = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
    
    if (!$stmt_acc->execute([$full_name, $email, $hashed, $role])) {
        echo json_encode(["status" => "error", "message" => "Registration failed"]);
        exit;
    }

    $new_user_id = $conn->lastInsertId();

    // SET SESSION
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['name'] = $full_name;
    
    // 🔥 FIX: I-DEFINE ANG $redirect VARIABLE BAGO GAMITIN
    if ($role === 'driver') {
        $redirect = "dashboard.html";
    } elseif ($role === 'passenger') {
        $redirect = "passenger-dashboard.html";
    } else { // admin
        $redirect = "admin-dashboard.html";
    }


    echo json_encode([
        "status" => "success",
        "message" => "Registration successful!",
        "role" => $role,
        "redirect" => $redirect // Ngayon ay may value na ito
    ]);
    exit;

} else {
    echo json_encode(["status" => "error", "message" => "Direct access not allowed."]);
    exit;
}
?>