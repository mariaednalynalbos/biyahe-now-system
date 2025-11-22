<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$account_id = $_SESSION['account_id'];

try {
    // Update passenger data
    $sql = "
        UPDATE passengers 
        SET firstname = ?, lastname = ?, contact_number = ?, 
            address = ?, gender = ?, date_of_birth = ?
        WHERE account_id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['firstName'],
        $_POST['lastName'], 
        $_POST['contactNumber'],
        $_POST['address'],
        $_POST['gender'],
        $_POST['dateOfBirth'],
        $account_id
    ]);
    
    // Update email in users table
    if (isset($_POST['email'])) {
        $emailSql = "UPDATE users SET email = ? WHERE account_id = ?";
        $emailStmt = $pdo->prepare($emailSql);
        $emailStmt->execute([$_POST['email'], $account_id]);
    }
    
    // Update session name
    $_SESSION['first_name'] = $_POST['firstName'];
    
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>