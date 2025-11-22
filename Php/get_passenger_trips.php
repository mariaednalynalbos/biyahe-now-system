<?php
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Check session
if (!isset($_SESSION['passenger_id']) && !isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => "Not logged in", 'session_debug' => $_SESSION]);
    exit;
}

$passenger_id = $_SESSION['passenger_id'] ?? $_SESSION['account_id'];
$type = $_GET['type'] ?? 'upcoming';

try {
    if ($type === 'upcoming') {
        $sql = "
            SELECT 
                b.*, r.origin, r.destination, r.route_name
            FROM 
                bookings b
            LEFT JOIN 
                routes r ON b.route_id = r.route_id
            WHERE 
                b.passenger_id = :id
                AND (b.status IN ('Pending', 'Confirmed') OR b.status = '' OR b.status IS NULL)
            ORDER BY 
                b.booking_date ASC, b.departure_time ASC
            LIMIT 10
        ";
    } else {
        $sql = "
            SELECT 
                b.*, r.origin, r.destination, r.route_name
            FROM 
                bookings b
            LEFT JOIN 
                routes r ON b.route_id = r.route_id
            WHERE 
                b.passenger_id = :id 
                AND (
                    b.status IN ('Completed', 'Cancelled') 
                    OR (b.booking_date < CURDATE() OR (b.booking_date = CURDATE() AND b.departure_time < CURTIME()))
                )
            ORDER BY 
                b.booking_date DESC, b.departure_time DESC
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $passenger_id);
    $stmt->execute();
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all bookings for debug
    $debugSql = "SELECT * FROM bookings WHERE passenger_id = :id ORDER BY booking_date DESC";
    $debugStmt = $pdo->prepare($debugSql);
    $debugStmt->bindParam(':id', $passenger_id);
    $debugStmt->execute();
    $allBookings = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'trips' => $trips, 
        'debug' => [
            'passenger_id' => $passenger_id, 
            'type' => $type, 
            'count' => count($trips),
            'all_bookings' => $allBookings
        ]
    ]);

} catch (PDOException $e) {
    error_log("Fetch Trips PDO Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => "Database error: " . $e->getMessage()]);
}
?>