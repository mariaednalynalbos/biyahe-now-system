<?php
header('Content-Type: application/json');

$routeId = $_GET['route_id'] ?? '';
$departureTime = $_GET['departure_time'] ?? '';

$response = ["success" => true, "occupied_seats" => []];

if (empty($routeId) || empty($departureTime)) {
    echo json_encode($response);
    exit;
}

$bookingsFile = __DIR__ . '/bookings.json';
if (!file_exists($bookingsFile)) {
    echo json_encode($response);
    exit;
}

$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

// Find occupied seats for this route and time
$occupiedSeats = [];
foreach ($bookings as $booking) {
    if ($booking['route'] == $routeId && $booking['trip_time'] == $departureTime && $booking['status'] !== 'Cancelled') {
        $occupiedSeats[] = $booking['seat_number'];
    }
}

$response['occupied_seats'] = $occupiedSeats;
echo json_encode($response);
?>