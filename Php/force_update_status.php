<?php
require_once 'db.php';

try {
    // Force update all bookings to Pending status
    $sql = "UPDATE bookings SET status = 'Pending'";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    
    echo "Force updated $affected bookings<br>";
    
    // Verify the update
    $checkSql = "SELECT booking_id, passenger_name, status FROM bookings ORDER BY booking_id DESC LIMIT 5";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $bookings = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>After Update:</h3>";
    foreach ($bookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}'<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>