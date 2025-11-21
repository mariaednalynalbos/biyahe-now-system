<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Update the specific booking ID 65
    $updateSql = "UPDATE bookings SET status = 'Pending' WHERE booking_id = 65";
    $updateStmt = $pdo->prepare($updateSql);
    $result = $updateStmt->execute();
    
    // Check the booking after update
    $checkSql = "SELECT * FROM bookings WHERE booking_id = 65";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Updated booking 65',
        'booking_after_update' => $booking
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>