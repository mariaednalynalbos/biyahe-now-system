<?php
require_once 'db.php';

try {
    // Create driver_assignments table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS driver_assignments (
        assignment_id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT NOT NULL,
        route_id INT NOT NULL,
        departure_time TIME NOT NULL,
        assigned_date DATE NOT NULL,
        status ENUM('Available', 'On Trip') DEFAULT 'Available',
        FOREIGN KEY (driver_id) REFERENCES drivers(driver_id)
    )";
    
    $pdo->exec($sql);
    
    // Add status column to drivers table if not exists
    $pdo->exec("ALTER TABLE drivers ADD COLUMN IF NOT EXISTS status ENUM('Available', 'On Trip') DEFAULT 'Available'");
    
    echo "Driver assignments table created successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>