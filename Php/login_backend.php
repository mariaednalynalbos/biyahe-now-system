<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

include "db.php";

$response = ["status" => "error", "message" => "Unexpected error occurred."];

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception("Please enter both email and password.");
    }

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    
    // Debug: Log login attempt
    error_log("Login attempt for email: $email");
   
    // Check accounts table
    $stmt = $conn->prepare(
        "SELECT account_id, email, password, role 
         FROM accounts 
         WHERE email = ?"
    );

    $stmt->execute([$email]);
    $account = $stmt->fetch();

    if (!$account) {
        throw new Exception("Email not found.");
    }

    // Check password - handle both hashed and plain text (for debugging)
    $passwordValid = false;
    
    if (password_verify($password, $account['password'])) {
        $passwordValid = true;
    } elseif ($password === $account['password']) {
        // Temporary: Allow plain text passwords (will be fixed by fix script)
        $passwordValid = true;
        error_log("WARNING: Plain text password detected for $email");
    }
    
    if (!$passwordValid) {
        error_log("Password verification failed for $email");
        throw new Exception("Incorrect password.");
    }

    // Save basic session data
    $_SESSION['account_id'] = $account['account_id'];
    $_SESSION['email'] = $account['email'];
    $_SESSION['role'] = $account['role'];
    $_SESSION['logged_in'] = true;

    // Load profile based on role
    if ($account['role'] === 'driver') {
        $profileSQL = "SELECT * FROM drivers WHERE account_id = ?";
    } 
    elseif ($account['role'] === 'passenger') {
        $profileSQL = "SELECT * FROM passengers WHERE account_id = ?";
    } 
    else {
        $profileSQL = "SELECT * FROM admins WHERE account_id = ?";
    }

    $pstmt = $conn->prepare($profileSQL);
    $pstmt->execute([$account['account_id']]);
    $profile = $pstmt->fetch();

    if (!$profile) {
        throw new Exception("Profile not found for this account.");
    }

    // Save all profile fields in session
    foreach ($profile as $key => $value) {
        $_SESSION[$key] = $value;
    }

    // 🔥 UNIVERSAL NAME DETECTION FIX
    $possibleFirstNames = ['firstname', 'first_name', 'fname', 'name'];
    $possibleLastNames  = ['lastname', 'last_name', 'lname'];

    $_SESSION['first_name'] = '';
    $_SESSION['last_name'] = '';

    foreach ($possibleFirstNames as $field) {
        if (!empty($profile[$field])) {
            $_SESSION['first_name'] = $profile[$field];
            break;
        }
    }

    foreach ($possibleLastNames as $field) {
        if (!empty($profile[$field])) {
            $_SESSION['last_name'] = $profile[$field];
            break;
        }
    }

    // Redirect based on role
   if ($account['role'] === 'driver') {
        $redirect = "Php/Driver-dashboard.php";
    } elseif ($account['role'] === 'passenger') {
        $redirect = "Php/Passenger-dashboard.php";
    } else {
        $redirect = "Php/Admin-dashboard.php";
    }

    $response = [
        "status" => "success",
        "message" => "Login successful!",
        "role" => $account['role'],
        "redirect" => $redirect
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
