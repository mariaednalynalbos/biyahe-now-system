<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Try different approaches to update the status
    
    // Method 1: Direct update with explicit value
    $sql1 = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $stmt1 = $pdo->prepare($sql1);
    $result1 = $stmt1->execute(['Pending', 65]);
    
    // Method 2: Check if there's a constraint or trigger
    $sql2 = "DESCRIBE bookings";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $columns = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Method 3: Check current value
    $sql3 = "SELECT booking_id, status, LENGTH(status) as status_length FROM bookings WHERE booking_id = 65";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute();
    $current = $stmt3->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'update_result' => $result1,
        'table_structure' => $columns,
        'current_booking' => $current
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>