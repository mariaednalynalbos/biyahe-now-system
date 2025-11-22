<?php
require_once 'db.php';

try {
    // Modify the ENUM to include 'Pending'
    $sql = "ALTER TABLE bookings MODIFY COLUMN status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed', 'Arrived') NOT NULL DEFAULT 'Pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "Successfully modified status column to include 'Pending'<br>";
    
    // Update all existing 'Confirmed' bookings to 'Pending'
    $updateSql = "UPDATE bookings SET status = 'Pending' WHERE status = 'Confirmed'";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute();
    $affected = $updateStmt->rowCount();
    
    echo "Updated $affected bookings from 'Confirmed' to 'Pending'<br><br>";
    
    // Verify the changes
    $checkSql = "SELECT booking_id, passenger_name, status FROM bookings ORDER BY booking_id DESC LIMIT 5";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $bookings = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Updated Bookings:</h3>";
    foreach ($bookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}'<br>";
    }
    
    // Show new column structure
    $descSql = "SHOW COLUMNS FROM bookings LIKE 'status'";
    $descStmt = $pdo->prepare($descSql);
    $descStmt->execute();
    $column = $descStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<br><h3>New Column Structure:</h3>";
    print_r($column);
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>