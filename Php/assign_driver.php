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
    // Check if driver_assignments table exists, if not create it
    $checkTable = $pdo->query("SHOW TABLES LIKE 'driver_assignments'");
    if ($checkTable->rowCount() == 0) {
        $createTable = "CREATE TABLE driver_assignments (
            assignment_id INT AUTO_INCREMENT PRIMARY KEY,
            driver_id INT NOT NULL,
            route_id INT NOT NULL,
            departure_time TIME NOT NULL,
            assigned_date DATE NOT NULL,
            status ENUM('Available', 'On Trip') DEFAULT 'Available'
        )";
        $pdo->exec($createTable);
    }
    
    // Remove existing assignment for this driver
    $deleteStmt = $pdo->prepare("DELETE FROM driver_assignments WHERE driver_id = :driver_id");
    $deleteStmt->bindParam(':driver_id', $driver_id);
    $deleteStmt->execute();
    
    // Insert new assignment
    $insertStmt = $pdo->prepare("
        INSERT INTO driver_assignments (driver_id, route_id, departure_time, assigned_date, status) 
        VALUES (:driver_id, :route_id, :departure_time, CURDATE(), 'Available')
    ");
    $insertStmt->bindParam(':driver_id', $driver_id);
    $insertStmt->bindParam(':route_id', $route_id);
    $insertStmt->bindParam(':departure_time', $departure_time);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Driver assigned successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to assign driver']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>