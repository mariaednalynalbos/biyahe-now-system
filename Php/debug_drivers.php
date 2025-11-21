<?php
require_once 'db.php';

echo "<h3>Testing Drivers Query</h3>";

try {
    // Simple test
    $sql = "SELECT * FROM drivers LIMIT 2";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($drivers);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>