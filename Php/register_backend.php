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
    $stmt = $conn->prepare("SELECT account_id FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered!"]);
        exit;
    }
    $stmt->close();

    // INSERT INTO ACCOUNTS
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt_acc = $conn->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
    $stmt_acc->bind_param("sss", $email, $hashed, $role);

    if (!$stmt_acc->execute()) {
        echo json_encode(["status" => "error", "message" => "Account Insert Error: " . $stmt_acc->error]);
        exit;
    }

    $new_account_id = $stmt_acc->insert_id;
    $stmt_acc->close();

    // INSERT INTO PROFILE TABLE
    if ($role === "passenger") {
        $sql_profile = "INSERT INTO passengers (account_id, lastname, firstname) VALUES (?, ?, ?)";
    } elseif ($role === "driver") {
        $sql_profile = "INSERT INTO drivers (account_id, lastname, firstname) VALUES (?, ?, ?)";
    } else {
        $sql_profile = "INSERT INTO admins (account_id, lastname, firstname) VALUES (?, ?, ?)";
    }

    $stmt_prof = $conn->prepare($sql_profile);
    $stmt_prof->bind_param("iss", $new_account_id, $lastname, $firstname);

    if (!$stmt_prof->execute()) {
        // rollback account insert
        $conn->query("DELETE FROM accounts WHERE account_id = $new_account_id");

        echo json_encode(["status" => "error", "message" => "Profile Insert Error: " . $stmt_prof->error]);
        exit;
    }

    $stmt_prof->close();

    // SET SESSION
    $_SESSION['account_id'] = $new_account_id;
    $_SESSION['email'] = $email;
    $_SESSION['role']  = $role;
    $_SESSION['first_name'] = $firstname;
    
    // 🔥 FIX: I-DEFINE ANG $redirect VARIABLE BAGO GAMITIN
    if ($role === 'driver') {
        // Tiyakin na ang file name ay Driver-dashboard.php (Capital D)
        $redirect = "Php/Driver-dashboard.php";
    } elseif ($role === 'passenger') {
        // Tiyakin na ang file name ay Passenger-dashboard.php (Capital P)
        $redirect = "Php/Passenger-dashboard.php";
    } else { // admin
        // Tiyakin na ang file name ay Admin-dashboard.php (Capital A)
        $redirect = "Php/Admin-dashboard.php";
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