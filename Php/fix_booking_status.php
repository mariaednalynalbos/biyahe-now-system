<?php
require_once 'db.php';

try {
    // Update all bookings with empty status to 'Pending'
    $sql = "UPDATE bookings SET status = 'Pending' WHERE status = '' OR status IS NULL";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute();
    
    $affected = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Updated $affected bookings to Pending status",
        'affected_rows' => $affected
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>