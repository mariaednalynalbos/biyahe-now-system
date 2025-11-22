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
    // Get passenger data with user email
    $sql = "
        SELECT 
            p.firstname, p.lastname, p.contact_number, p.address, 
            p.gender, p.date_of_birth, u.email
        FROM passengers p
        LEFT JOIN users u ON p.account_id = u.account_id
        WHERE p.account_id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$account_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profile) {
        echo json_encode([
            'success' => true,
            'data' => $profile
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Profile not found']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>