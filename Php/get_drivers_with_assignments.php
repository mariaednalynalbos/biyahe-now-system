<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Check if driver_assignments table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'driver_assignments'");
    
    if ($checkTable->rowCount() > 0) {
        // Table exists, use JOIN query
        $sql = "
            SELECT 
                d.driver_id,
                d.firstname,
                d.lastname,
                d.contact,
                d.license_number,
                d.plate_number,
                da.status as assignment_status,
                da.route_id,
                da.departure_time,
                r.route_name,
                r.origin,
                r.destination
            FROM drivers d
            LEFT JOIN driver_assignments da ON d.driver_id = da.driver_id
            LEFT JOIN routes r ON da.route_id = r.route_id
            ORDER BY d.lastname, d.firstname
        ";
    } else {
        // Table doesn't exist, use simple query
        $sql = "
            SELECT 
                driver_id,
                firstname,
                lastname,
                contact,
                license_number,
                plate_number,
                'Unassigned' as assignment_status,
                NULL as route_id,
                NULL as departure_time,
                NULL as route_name,
                NULL as origin,
                NULL as destination
            FROM drivers 
            ORDER BY lastname, firstname
        ";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'drivers' => $drivers
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>