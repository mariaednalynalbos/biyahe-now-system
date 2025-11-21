<?php
require_once 'db.php';

try {
    // Create simple driver_assignments table
    $sql = "CREATE TABLE IF NOT EXISTS driver_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT NOT NULL,
        route_id INT NOT NULL,
        departure_time TIME NOT NULL,
        status ENUM('Available', 'On Trip') DEFAULT 'Available',
        assigned_date DATE DEFAULT CURRENT_DATE
    )";
    
    $pdo->exec($sql);
    echo "Driver assignments table created successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>