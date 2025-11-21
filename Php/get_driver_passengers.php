<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Get driver_id from drivers table using account_id
$account_id = $_SESSION['account_id'] ?? '';
$driver_id = '';

if ($account_id) {
    try {
        $driverSql = "SELECT driver_id FROM drivers WHERE account_id = ? OR driver_id = ?";
        $driverStmt = $pdo->prepare($driverSql);
        $driverStmt->execute([$account_id, $account_id]);
        $driverResult = $driverStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($driverResult) {
            $driver_id = $driverResult['driver_id'];
        }
    } catch (PDOException $e) {
        $driver_id = $account_id;
    }
}

if (empty($driver_id)) {
    echo json_encode(['success' => false, 'message' => 'Driver not found', 'passengers' => []]);
    exit;
}

try {
    // Get driver's assigned route and time
    $assignSql = "SELECT route_id, time_slot FROM driver_assignments WHERE driver_id = ? AND status IN ('Available', 'On Trip')";
    $assignStmt = $pdo->prepare($assignSql);
    $assignStmt->execute([$driver_id]);
    $assignment = $assignStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$assignment) {
        echo json_encode(['success' => true, 'passengers' => [], 'message' => 'No route assigned']);
        exit;
    }
    
    // Get passengers for this route and time today
    $passengerSql = "
        SELECT 
            booking_id,
            passenger_name,
            seat_number,
            contact_number,
            booking_date,
            departure_time,
            status
        FROM bookings 
        WHERE route_id = ? 
        AND departure_time = ? 
        AND booking_date = CURDATE() 
        AND status IN ('Pending', 'Confirmed')
        ORDER BY seat_number
    ";
    $passengerStmt = $pdo->prepare($passengerSql);
    $passengerStmt->execute([$assignment['route_id'], $assignment['time_slot']]);
    $passengers = $passengerStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'passengers' => $passengers,
        'route_id' => $assignment['route_id'],
        'time_slot' => $assignment['time_slot']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error', 'passengers' => []]);
}
?>