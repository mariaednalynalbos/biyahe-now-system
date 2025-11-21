<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$driver_id = $_POST['driver_id'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($driver_id) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Update driver status in driver_assignments table
    $sql = "UPDATE driver_assignments SET status = :status WHERE driver_id = :driver_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':driver_id', $driver_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Driver status updated to {$status} successfully"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update driver status']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>