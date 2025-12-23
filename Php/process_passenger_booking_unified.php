<?php
session_start();
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Booking failed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = "Please log in to book a trip";
        echo json_encode($response);
        exit;
    }

    // Get form data
    $firstName = trim($_POST['firstName'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $route = trim($_POST['route'] ?? '');
    $tripTime = trim($_POST['tripTime'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $seatNumber = trim($_POST['seatNumber'] ?? '');

    // Validate required fields
    if (empty($firstName) || empty($surname) || empty($route) || empty($tripTime) || empty($contactNumber) || empty($seatNumber)) {
        $response['message'] = "Please fill in all required fields";
        echo json_encode($response);
        exit;
    }

    // Create bookings file if it doesn't exist
    $bookingsFile = __DIR__ . '/bookings.json';
    if (!file_exists($bookingsFile)) {
        file_put_contents($bookingsFile, json_encode([]));
    }

    // Read existing bookings
    $bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

    // Create new booking
    $booking = [
        'id' => count($bookings) + 1,
        'user_id' => $_SESSION['user_id'],
        'passenger_name' => $firstName . ' ' . $surname,
        'route' => $route,
        'trip_time' => $tripTime,
        'contact_number' => $contactNumber,
        'seat_number' => $seatNumber,
        'booking_date' => date('Y-m-d'),
        'booking_time' => date('H:i:s'),
        'status' => 'Confirmed'
    ];

    // Add booking to array
    $bookings[] = $booking;

    // Save to file
    if (file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT))) {
        $response = [
            "success" => true,
            "message" => "Trip booked successfully!",
            "booking_id" => $booking['id']
        ];
    } else {
        $response['message'] = "Failed to save booking. Please try again.";
    }
}

echo json_encode($response);
?>