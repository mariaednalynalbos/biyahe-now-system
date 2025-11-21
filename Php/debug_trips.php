<?php
session_start();
require_once 'db.php';

echo "<h3>Debug Info:</h3>";
echo "<p>Session Data:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

$passenger_id = $_SESSION['passenger_id'] ?? $_SESSION['account_id'] ?? null;
echo "<p>Using passenger_id: " . $passenger_id . "</p>";

if ($passenger_id) {
    try {
        // Check all bookings for this passenger
        $sql = "SELECT b.*, r.origin, r.destination, r.route_name 
                FROM bookings b 
                JOIN routes r ON b.route_id = r.route_id 
                WHERE b.passenger_id = :id 
                ORDER BY b.booking_date DESC, b.departure_time DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $passenger_id);
        $stmt->execute();
        $allBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>All Bookings (" . count($allBookings) . "):</p>";
        echo "<pre>";
        print_r($allBookings);
        echo "</pre>";
        
        // Check upcoming trips specifically
        $current_date = date("Y-m-d");
        $current_time = date("H:i:s");
        
        $upcomingSql = "SELECT b.*, r.origin, r.destination, r.route_name 
                        FROM bookings b 
                        JOIN routes r ON b.route_id = r.route_id 
                        WHERE b.passenger_id = :id 
                        AND b.status = 'Confirmed'
                        AND (b.booking_date > CURDATE() 
                             OR (b.booking_date = CURDATE() AND b.departure_time >= CURTIME()))
                        ORDER BY b.booking_date ASC, b.departure_time ASC 
                        LIMIT 5";
        
        $stmt2 = $pdo->prepare($upcomingSql);
        $stmt2->bindParam(':id', $passenger_id);
        $stmt2->execute();
        $upcomingTrips = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Upcoming Trips (" . count($upcomingTrips) . "):</p>";
        echo "<pre>";
        print_r($upcomingTrips);
        echo "</pre>";
        
        echo "<p>Current Date: " . $current_date . "</p>";
        echo "<p>Current Time: " . $current_time . "</p>";
        
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>No passenger_id found in session</p>";
}
?>