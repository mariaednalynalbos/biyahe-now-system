<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Get all bookings
    $bookingsSql = "SELECT * FROM bookings ORDER BY booking_id DESC LIMIT 5";
    $bookingsStmt = $pdo->prepare($bookingsSql);
    $bookingsStmt->execute();
    $bookings = $bookingsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all driver assignments
    $assignmentsSql = "SELECT * FROM driver_assignments";
    $assignmentsStmt = $pdo->prepare($assignmentsSql);
    $assignmentsStmt->execute();
    $assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'recent_bookings' => $bookings,
        'driver_assignments' => $assignments,
        'session' => $_SESSION
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>