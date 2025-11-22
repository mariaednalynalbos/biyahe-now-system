<?php
require_once 'db.php';

try {
    echo "Attempting to repair bookings table...<br>";
    
    // Repair the crashed table
    $repairSql = "REPAIR TABLE bookings";
    $pdo->exec($repairSql);
    echo "✅ Table repair completed<br><br>";
    
    // Check table status
    $checkSql = "CHECK TABLE bookings";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Table Status:</h3>";
    foreach ($result as $row) {
        echo "Table: {$row['Table']}, Status: {$row['Msg_text']}<br>";
    }
    
    // Test if we can query the table now
    echo "<br><h3>Testing Table Access:</h3>";
    $testSql = "SELECT COUNT(*) as count FROM bookings";
    $testStmt = $pdo->prepare($testSql);
    $testStmt->execute();
    $count = $testStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total bookings: {$count['count']}<br>";
    
    // Show recent bookings
    $recentSql = "SELECT booking_id, passenger_name, status FROM bookings ORDER BY booking_id DESC LIMIT 5";
    $recentStmt = $pdo->prepare($recentSql);
    $recentStmt->execute();
    $bookings = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Recent Bookings:</h3>";
    foreach ($bookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}'<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Repair failed: " . $e->getMessage() . "<br>";
    echo "<br><strong>Alternative: Recreate the table</strong><br>";
    echo "<a href='recreate_bookings.php'>Click here to recreate bookings table</a>";
}
?>