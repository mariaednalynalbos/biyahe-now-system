<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$assignments = $input['assignments'] ?? [];

if (empty($assignments) || !is_array($assignments)) {
    echo json_encode(['success' => false, 'message' => 'No assignments provided']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Create driver_assignments table if it doesn't exist
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS driver_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            driver_id INT NOT NULL,
            route_id INT NOT NULL,
            departure_time TIME NOT NULL,
            status VARCHAR(20) DEFAULT 'Available',
            assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_assignment (driver_id, route_id, departure_time)
        )
    ";
    $pdo->exec($createTableSql);
    
    $insertSql = "
        INSERT INTO driver_assignments (driver_id, route_id, departure_time, status) 
        VALUES (?, ?, ?, 'Available')
        ON DUPLICATE KEY UPDATE 
        status = 'Available', 
        assigned_date = CURRENT_TIMESTAMP
    ";
    $stmt = $pdo->prepare($insertSql);
    
    $successCount = 0;
    foreach ($assignments as $assignment) {
        $driver_id = $assignment['driver_id'] ?? '';
        $route_id = $assignment['route_id'] ?? '';
        $departure_time = $assignment['departure_time'] ?? '';
        
        if (empty($driver_id) || empty($route_id) || empty($departure_time)) {
            continue;
        }
        
        if ($stmt->execute([$driver_id, $route_id, $departure_time])) {
            $successCount++;
        }
    }
    
    if ($successCount > 0) {
        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'message' => "$successCount driver(s) assigned successfully"
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No drivers were assigned']);
    }
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>