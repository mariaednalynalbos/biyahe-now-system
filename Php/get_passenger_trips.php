<?php
session_start();
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Failed to load trips"];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = "Not logged in";
    echo json_encode($response);
    exit;
}

$type = $_GET['type'] ?? 'upcoming';
$bookingsFile = __DIR__ . '/bookings.json';

if (!file_exists($bookingsFile)) {
    $response = ["success" => true, "trips" => []];
    echo json_encode($response);
    exit;
}

$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

// Filter bookings for current user
$userBookings = array_filter($bookings, function($booking) {
    return $booking['user_id'] == $_SESSION['user_id'];
});

// Add route names and destinations
$routes = [
    '1' => ['name' => 'Naval to Tacloban', 'destination' => 'Tacloban'],
    '2' => ['name' => 'Naval to Ormoc', 'destination' => 'Ormoc'],
    '3' => ['name' => 'Naval to Lemon', 'destination' => 'Lemon']
];

$trips = [];
foreach ($userBookings as $booking) {
    $routeInfo = $routes[$booking['route']] ?? ['name' => 'Unknown Route', 'destination' => 'Unknown'];
    
    $trips[] = [
        'booking_id' => $booking['id'],
        'passenger_name' => $booking['passenger_name'],
        'route_name' => $routeInfo['name'],
        'destination' => $routeInfo['destination'],
        'booking_date' => $booking['booking_date'],
        'departure_time' => $booking['trip_time'],
        'seat_number' => $booking['seat_number'],
        'status' => $booking['status'],
        'route_id' => $booking['route']
    ];
}

$response = [
    "success" => true,
    "trips" => $trips
];

echo json_encode($response);
?>