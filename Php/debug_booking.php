<?php
session_start();
require_once 'db.php';

// Check session
echo "Session passenger_id: " . ($_SESSION['passenger_id'] ?? 'NOT SET') . "<br>";
echo "Session account_id: " . ($_SESSION['account_id'] ?? 'NOT SET') . "<br>";

// Check bookings table structure
try {
    $stmt = $pdo->query("DESCRIBE bookings");
    echo "<h3>Bookings Table Structure:</h3>";
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
    
    // Check recent bookings
    $stmt2 = $pdo->query("SELECT * FROM bookings ORDER BY booking_id DESC LIMIT 5");
    echo "<h3>Recent Bookings:</h3>";
    while ($row = $stmt2->fetch()) {
        print_r($row);
        echo "<br><br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>