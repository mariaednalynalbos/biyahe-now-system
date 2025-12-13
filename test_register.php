<?php
header('Content-Type: application/json');

try {
    include "Php/db.php";
    
    // Test users table exists
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo json_encode([
        "status" => "success", 
        "message" => "Users table accessible",
        "user_count" => $result['count']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
?>