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
        // Fallback to account_id
        $driver_id = $account_id;
    }
}

if (empty($driver_id)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Driver not found or not logged in', 
        'count' => 0,
        'debug' => ['account_id' => $account_id]
    ]);
    exit;
}

try {
    // Check if driver_assignments table exists, create if not
    $checkTable = "SHOW TABLES LIKE 'driver_assignments'";
    $tableExists = $pdo->query($checkTable)->rowCount() > 0;
    
    if (!$tableExists) {
        $createTable = "
            CREATE TABLE driver_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                driver_id VARCHAR(50),
                route_id INT,
                time_slot TIME,
                status ENUM('Available', 'On Trip', 'Unassigned') DEFAULT 'Unassigned',
                assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $pdo->exec($createTable);
    }
    
    // Get driver's assigned route and time
    $assignSql = "SELECT route_id, time_slot FROM driver_assignments WHERE driver_id = ? AND status IN ('Available', 'On Trip')";
    $assignStmt = $pdo->prepare($assignSql);
    $assignStmt->execute([$driver_id]);
    $assignment = $assignStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$assignment) {
        echo json_encode(['success' => true, 'count' => 0, 'message' => 'No route assigned']);
        exit;
    }
    
    // Count PENDING passengers for this route and time today (for notifications)
    $passengerSql = "
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE route_id = ? 
        AND departure_time = ? 
        AND booking_date = CURDATE() 
        AND status = 'Pending'
    ";
    $passengerStmt = $pdo->prepare($passengerSql);
    $passengerStmt->execute([$assignment['route_id'], $assignment['time_slot']]);
    $result = $passengerStmt->fetch(PDO::FETCH_ASSOC);
    
    // Also get all bookings for debugging
    $debugSql = "SELECT * FROM bookings WHERE route_id = ? AND departure_time = ? AND booking_date = CURDATE()";
    $debugStmt = $pdo->prepare($debugSql);
    $debugStmt->execute([$assignment['route_id'], $assignment['time_slot']]);
    $allBookings = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count total passengers (Pending + Confirmed)
    $totalSql = "SELECT COUNT(*) as total FROM bookings WHERE route_id = ? AND departure_time = ? AND booking_date = CURDATE() AND status IN ('Pending', 'Confirmed')";
    $totalStmt = $pdo->prepare($totalSql);
    $totalStmt->execute([$assignment['route_id'], $assignment['time_slot']]);
    $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'count' => (int)$result['count'], // Pending count for notifications
        'total_passengers' => (int)$totalResult['total'], // Total passengers
        'route_id' => $assignment['route_id'],
        'time_slot' => $assignment['time_slot'],
        'debug' => [
            'driver_id' => $driver_id,
            'account_id' => $account_id,
            'assignment' => $assignment,
            'pending_count' => $result['count'],
            'total_count' => $totalResult['total'],
            'all_bookings_today' => $allBookings
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error', 'count' => 0]);
}
?>