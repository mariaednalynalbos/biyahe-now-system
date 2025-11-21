<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Update bookings with empty status to Pending
    $updateSql = "UPDATE bookings SET status = 'Pending' WHERE status = '' OR status IS NULL";
    $updateStmt = $pdo->prepare($updateSql);
    $result = $updateStmt->execute();
    
    $rowsAffected = $updateStmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Updated {$rowsAffected} bookings to Pending status"
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>