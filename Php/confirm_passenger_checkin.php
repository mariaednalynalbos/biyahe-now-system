<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$booking_id = $_POST['booking_id'] ?? '';

if (empty($booking_id)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID required']);
    exit;
}

try {
    // Get booking details
    $bookingSql = "SELECT * FROM bookings WHERE booking_id = ?";
    $bookingStmt = $pdo->prepare($bookingSql);
    $bookingStmt->execute([$booking_id]);
    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    // Update booking status to Confirmed
    $updateSql = "UPDATE bookings SET status = 'Confirmed' WHERE booking_id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateResult = $updateStmt->execute([$booking_id]);
    
    if (!$updateResult) {
        echo json_encode(['success' => false, 'message' => 'Failed to update booking status']);
        exit;
    }
    
    // Create passenger notification
    try {
        // Create notifications table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS passenger_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                passenger_id INT,
                message TEXT,
                booking_id INT,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $notifSql = "INSERT INTO passenger_notifications (passenger_id, message, booking_id) VALUES (?, ?, ?)";
        $notifStmt = $pdo->prepare($notifSql);
        $message = "Your booking has been confirmed by the driver for seat {$booking['seat_number']}. Status changed from Pending to Confirmed.";
        $notifStmt->execute([$booking['passenger_id'], $message, $booking_id]);
        
    } catch (PDOException $e) {
        // Notification failed but check-in succeeded
        error_log("Passenger notification error: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Booking confirmed successfully',
        'booking' => $booking
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>