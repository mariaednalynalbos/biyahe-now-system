<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Simple query to get all drivers with basic info
    $sql = "SELECT driver_id, firstname, lastname, contact, license_number, plate_number FROM drivers ORDER BY lastname, firstname";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add assignment status for each driver
    foreach ($drivers as &$driver) {
        // Default values
        $driver['status'] = 'Unassigned';
        $driver['route_name'] = null;
        $driver['origin'] = null;
        $driver['destination'] = null;
        $driver['time_slot'] = null;
        
        // Check for assignment
        try {
            $assignSql = "SELECT status, route_id, time_slot FROM driver_assignments WHERE driver_id = ?";
            $assignStmt = $pdo->prepare($assignSql);
            $assignStmt->execute([$driver['driver_id']]);
            $assignment = $assignStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($assignment) {
                $driver['status'] = $assignment['status'];
                $driver['time_slot'] = $assignment['time_slot'];
                
                // Set route info based on route_id
                if ($assignment['route_id'] == 1) {
                    $driver['route_name'] = 'Naval to Tacloban';
                    $driver['origin'] = 'Naval';
                    $driver['destination'] = 'Tacloban';
                } elseif ($assignment['route_id'] == 2) {
                    $driver['route_name'] = 'Naval to Ormoc';
                    $driver['origin'] = 'Naval';
                    $driver['destination'] = 'Ormoc';
                }
            }
        } catch (PDOException $e) {
            // Assignment table doesn't exist yet, keep defaults
        }
    }
    
    echo json_encode([
        'success' => true, 
        'drivers' => $drivers
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>