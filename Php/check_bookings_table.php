<?php
require_once 'db.php';

try {
    // Check table structure
    $sql = "DESCRIBE bookings";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if status column exists
    $statusExists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'status') {
            $statusExists = true;
            break;
        }
    }
    
    // Add status column if it doesn't exist
    if (!$statusExists) {
        $alterSql = "ALTER TABLE bookings ADD COLUMN status VARCHAR(20) DEFAULT 'Pending'";
        $pdo->exec($alterSql);
        echo "Status column added successfully<br>";
    }
    
    // Update all existing bookings to have 'Pending' status
    $updateSql = "UPDATE bookings SET status = 'Pending' WHERE status IS NULL OR status = ''";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute();
    $affected = $updateStmt->rowCount();
    
    echo "Updated $affected bookings to Pending status<br>";
    
    // Show current bookings
    $selectSql = "SELECT booking_id, passenger_name, status, booking_date FROM bookings ORDER BY booking_id DESC LIMIT 10";
    $selectStmt = $pdo->prepare($selectSql);
    $selectStmt->execute();
    $bookings = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Recent Bookings:</h3>";
    foreach ($bookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}', Date: {$booking['booking_date']}<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>