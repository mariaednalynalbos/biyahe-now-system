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

    // Try Supabase first
    try {
        include "supabase_db.php";
        $users = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
        
        if (!empty($users)) {
            $user = $users[0];
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
                $_SESSION['role'] = $user['user_type'] ?? 'passenger';
                
                $response = [
                    "success" => true,
                    "message" => "Login successful",
                    "redirect" => $user['user_type'] === 'admin' ? "Php/Admin-dashboard.php" : "Php/Passenger-dashboard.php"
                ];
            } else {
                $response['message'] = "Invalid password";
            }
        } else {
            $response['message'] = "User not found";
        }
    } catch (Exception $e) {
        // Fallback to MySQL if Supabase fails
        try {
            include "db.php";
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['account_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role'] = $user['user_type'] ?? 'passenger';
                    
                    $response = [
                        "success" => true,
                        "message" => "Login successful (MySQL fallback)",
                        "redirect" => $user['user_type'] === 'admin' ? "Php/Admin-dashboard.php" : "Php/Passenger-dashboard.php"
                    ];
                } else {
                    $response['message'] = "Invalid password";
                }
            } else {
                $response['message'] = "User not found";
            }
            $stmt->close();
            $conn->close();
        } catch (Exception $mysql_error) {
            $response['message'] = "Database connection failed: " . $e->getMessage();
        }
    }
}

echo json_encode($response);
?>