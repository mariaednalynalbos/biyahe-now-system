<?php
require_once 'db.php';

try {
    // Check current column structure
    $sql = "SHOW COLUMNS FROM bookings LIKE 'status'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Current status column: ";
    print_r($column);
    echo "<br><br>";
    
    // Drop and recreate status column
    $dropSql = "ALTER TABLE bookings DROP COLUMN IF EXISTS status";
    $pdo->exec($dropSql);
    echo "Dropped status column<br>";
    
    $addSql = "ALTER TABLE bookings ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Pending'";
    $pdo->exec($addSql);
    echo "Added new status column<br>";
    
    // Update all existing records
    $updateSql = "UPDATE bookings SET status = 'Pending'";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute();
    $affected = $updateStmt->rowCount();
    echo "Updated $affected records<br><br>";
    
    // Verify
    $checkSql = "SELECT booking_id, passenger_name, status FROM bookings ORDER BY booking_id DESC LIMIT 5";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $bookings = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Verification:</h3>";
    foreach ($bookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}'<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>