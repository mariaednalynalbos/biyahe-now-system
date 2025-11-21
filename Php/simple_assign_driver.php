<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$driver_id = $_POST['driver_id'] ?? '';
$route_id = $_POST['route_id'] ?? '';
$departure_time = $_POST['departure_time'] ?? '';

if (empty($driver_id) || empty($route_id) || empty($departure_time)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS driver_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT,
        route_id INT,
        time_slot VARCHAR(20),
        status VARCHAR(20) DEFAULT 'Available'
    )");
    
    // Check if driver already has assignment
    $checkStmt = $pdo->prepare("SELECT id FROM driver_assignments WHERE driver_id = ?");
    $checkStmt->execute([$driver_id]);
    
    if ($checkStmt->fetch()) {
        // Update existing assignment
        $stmt = $pdo->prepare("UPDATE driver_assignments SET route_id = ?, time_slot = ?, status = 'Available' WHERE driver_id = ?");
        $result = $stmt->execute([$route_id, $departure_time, $driver_id]);
    } else {
        // Insert new assignment
        $stmt = $pdo->prepare("INSERT INTO driver_assignments (driver_id, route_id, time_slot, status) VALUES (?, ?, ?, 'Available')");
        $result = $stmt->execute([$driver_id, $route_id, $departure_time]);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Driver assigned successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to assign driver']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>