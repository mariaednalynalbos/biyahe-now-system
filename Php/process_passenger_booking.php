<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
header('Content-Type: application/json');

require_once 'db.php';

$passenger_id = $_SESSION['passenger_id'] ?? $_SESSION['account_id'] ?? null;

if (!$passenger_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

function json_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, "Invalid request method.");
}

// Get form data
$surname = trim($_POST['surname'] ?? '');
$firstName = trim($_POST['firstName'] ?? '');
$routeId = $_POST['route'] ?? '';
$tripTime = $_POST['tripTime'] ?? '';
$seatNumber = $_POST['seatNumber'] ?? '';
$contactNumber = $_POST['contactNumber'] ?? '';

if (empty($surname) || empty($firstName) || empty($routeId) || empty($tripTime) || empty($seatNumber) || empty($contactNumber)) {
    json_response(false, "Please fill in all required booking details.");
}

$passengerName = $firstName . ' ' . $surname;

try {
    // Insert booking directly using session passenger_id
    $sql = "
        INSERT INTO bookings (
            passenger_id,
            passenger_name, 
            route_id, 
            departure_time, 
            seat_number, 
            contact_number, 
            booking_date, 
            status
        ) VALUES (
            :passenger_id,
            :name, 
            :route, 
            :time, 
            :seat, 
            :contact, 
            CURDATE(), 
            'Pending'
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':passenger_id', $passenger_id);
    $stmt->bindParam(':name', $passengerName);
    $stmt->bindParam(':route', $routeId);
    $stmt->bindParam(':time', $tripTime);
    $stmt->bindParam(':seat', $seatNumber);
    $stmt->bindParam(':contact', $contactNumber);

    if (!$stmt->execute()) {
        json_response(false, "Database error: Could not save booking.");
    }

    // Get the last inserted booking with route info
    $booking_id = $pdo->lastInsertId();

    $sql2 = "
        SELECT b.*, r.origin, r.destination, r.route_name
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        WHERE b.booking_id = :booking_id
    ";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindParam(':booking_id', $booking_id);
    $stmt2->execute();
    $newBooking = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Check for assigned drivers on this route and time
    try {
        $driverSql = "
            SELECT d.driver_id, d.firstname, d.lastname, da.status
            FROM driver_assignments da
            JOIN drivers d ON da.driver_id = d.driver_id
            WHERE da.route_id = :route_id AND da.time_slot = :time_slot
        ";
        $driverStmt = $pdo->prepare($driverSql);
        $driverStmt->bindParam(':route_id', $routeId);
        $driverStmt->bindParam(':time_slot', $tripTime);
        $driverStmt->execute();
        $assignedDrivers = $driverStmt->fetchAll(PDO::FETCH_ASSOC);

        // Create notifications for assigned drivers
        if (!empty($assignedDrivers)) {
            $notifSql = "
                INSERT INTO driver_notifications (driver_id, message, booking_id, created_at) 
                VALUES (?, ?, ?, NOW())
            ";
            $notifStmt = $pdo->prepare($notifSql);
            
            // Create notifications table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS driver_notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    driver_id INT,
                    message TEXT,
                    booking_id INT,
                    is_read BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            foreach ($assignedDrivers as $driver) {
                $message = "New passenger booking: {$passengerName} booked seat {$seatNumber} for {$newBooking['route_name']} at {$tripTime}";
                $notifStmt->execute([$driver['driver_id'], $message, $booking_id]);
            }
        }
    } catch (PDOException $e) {
        // Notification failed but booking succeeded
        error_log("Driver notification error: " . $e->getMessage());
    }

    json_response(true, "Trip booked successfully!", $newBooking);

} catch (PDOException $e) {
    error_log("Booking PDO Error: " . $e->getMessage());
    json_response(false, "A critical error occurred. Please try again later. (Error Code: DB1)");
}
?>