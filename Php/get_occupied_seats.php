<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$route_id = $_GET['route_id'] ?? '';
$departure_time = $_GET['departure_time'] ?? '';
$booking_date = $_GET['booking_date'] ?? date('Y-m-d');

if (empty($route_id) || empty($departure_time)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $sql = "
        SELECT DISTINCT seat_number 
        FROM bookings 
        WHERE route_id = :route_id 
        AND departure_time = :departure_time 
        AND booking_date = :booking_date 
        AND status = 'Confirmed'
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':route_id', $route_id);
    $stmt->bindParam(':departure_time', $departure_time);
    $stmt->bindParam(':booking_date', $booking_date);
    $stmt->execute();
    
    $occupied_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true, 
        'occupied_seats' => $occupied_seats,
        'debug' => [
            'route_id' => $route_id,
            'departure_time' => $departure_time,
            'booking_date' => $booking_date,
            'count' => count($occupied_seats)
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>