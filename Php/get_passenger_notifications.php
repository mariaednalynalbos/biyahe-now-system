<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$passenger_id = $_SESSION['passenger_id'] ?? $_SESSION['account_id'] ?? '';

if (empty($passenger_id)) {
    echo json_encode(['success' => false, 'message' => 'Passenger ID required', 'notifications' => []]);
    exit;
}

try {
    $sql = "
        SELECT * FROM passenger_notifications 
        WHERE passenger_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$passenger_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'notifications' => $notifications,
        'unread_count' => count(array_filter($notifications, fn($n) => !$n['is_read']))
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'notifications' => [],
        'unread_count' => 0
    ]);
}
?>