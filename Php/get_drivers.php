<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Simple query - just get drivers
    $sql = "SELECT * FROM drivers ORDER BY lastname, firstname";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default assignment status
    foreach ($drivers as &$driver) {
        $driver['assignment_status'] = 'Unassigned';
        $driver['route_name'] = null;
        $driver['origin'] = null;
        $driver['destination'] = null;
        $driver['departure_time'] = null;
    }
    
    echo json_encode([
        'success' => true, 
        'drivers' => $drivers,
        'count' => count($drivers)
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>