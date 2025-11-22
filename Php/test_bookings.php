<?php
require_once 'db.php';

try {
    // Test basic table access
    $testSql = "SELECT COUNT(*) as count FROM bookings";
    $stmt = $pdo->prepare($testSql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Table is accessible<br>";
    echo "Total bookings: {$result['count']}<br><br>";
    
    // Show recent bookings
    $recentSql = "SELECT booking_id, passenger_name, status, booking_date FROM bookings ORDER BY booking_id DESC LIMIT 5";
    $stmt2 = $pdo->prepare($recentSql);
    $stmt2->execute();
    $bookings = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Recent Bookings:</h3>";
    foreach ($bookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}', Date: {$booking['booking_date']}<br>";
    }
    
    // Now fix the ENUM status
    echo "<br><h3>Fixing Status Column:</h3>";
    $alterSql = "ALTER TABLE bookings MODIFY COLUMN status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed', 'Arrived') NOT NULL DEFAULT 'Pending'";
    $pdo->exec($alterSql);
    echo "✅ Status column updated to include 'Pending'<br>";
    
    // Update existing bookings
    $updateSql = "UPDATE bookings SET status = 'Pending' WHERE status = 'Confirmed'";
    $stmt3 = $pdo->prepare($updateSql);
    $stmt3->execute();
    $affected = $stmt3->rowCount();
    echo "✅ Updated $affected bookings to 'Pending' status<br><br>";
    
    // Verify final result
    $finalSql = "SELECT booking_id, passenger_name, status FROM bookings ORDER BY booking_id DESC LIMIT 5";
    $stmt4 = $pdo->prepare($finalSql);
    $stmt4->execute();
    $finalBookings = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Final Result:</h3>";
    foreach ($finalBookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}'<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>