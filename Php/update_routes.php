<?php
require_once 'db.php';

try {
    // Clear existing routes
    $pdo->exec("DELETE FROM routes");
    
    // Insert only the 2 required routes
    $sql = "INSERT INTO routes (route_id, route_name, origin, destination) VALUES 
            (1, 'Naval-Tacloban', 'Naval', 'Tacloban'),
            (2, 'Naval-Ormoc', 'Naval', 'Ormoc')";
    
    $pdo->exec($sql);
    
    echo "Routes updated successfully!<br>";
    echo "Route 1: Naval → Tacloban<br>";
    echo "Route 2: Naval → Ormoc<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>