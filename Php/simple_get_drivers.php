<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

try {
    $sql = "SELECT driver_id, firstname, lastname, contact, license_number, plate_number FROM drivers ORDER BY lastname, firstname";
    
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