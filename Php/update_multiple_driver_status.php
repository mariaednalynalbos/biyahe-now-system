<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$driver_ids_json = $_POST['driver_ids'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($driver_ids_json) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$driver_ids = json_decode($driver_ids_json, true);
if (!is_array($driver_ids) || empty($driver_ids)) {
    echo json_encode(['success' => false, 'message' => 'Invalid driver IDs']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $placeholders = str_repeat('?,', count($driver_ids) - 1) . '?';
    $sql = "UPDATE driver_assignments SET status = ? WHERE driver_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    
    $params = array_merge([$status], $driver_ids);
    $result = $stmt->execute($params);
    
    if ($result) {
        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'message' => count($driver_ids) . " driver(s) status updated to {$status} successfully"
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update driver status']);
    }
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>