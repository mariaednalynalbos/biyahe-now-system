<?php
require_once 'db.php';

try {
    echo "Creating new bookings table...<br>";
    
    // Drop table if exists
    $dropSql = "DROP TABLE IF EXISTS bookings";
    $pdo->exec($dropSql);
    echo "✅ Dropped old table<br>";
    
    // Create new bookings table
    $createSql = "
        CREATE TABLE bookings (
            booking_id INT AUTO_INCREMENT PRIMARY KEY,
            passenger_id INT NOT NULL,
            passenger_name VARCHAR(100) NOT NULL,
            route_id INT NOT NULL,
            departure_time TIME NOT NULL,
            seat_number VARCHAR(10) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            booking_date DATE NOT NULL,
            status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed', 'Arrived') NOT NULL DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createSql);
    echo "✅ Created new bookings table<br>";
    
    // Insert sample booking for testing
    $insertSql = "
        INSERT INTO bookings (passenger_id, passenger_name, route_id, departure_time, seat_number, contact_number, booking_date, status)
        VALUES (11, 'Maria Albos', 1, '06:00:00', '4', '09066222206', '2025-11-22', 'Pending')
    ";
    $pdo->exec($insertSql);
    echo "✅ Added sample booking<br><br>";
    
    // Verify table structure
    $descSql = "DESCRIBE bookings";
    $stmt = $pdo->prepare($descSql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Table Structure:</h3>";
    foreach ($columns as $column) {
        echo "{$column['Field']} - {$column['Type']} - Default: {$column['Default']}<br>";
    }
    
    // Show sample data
    $selectSql = "SELECT * FROM bookings";
    $stmt2 = $pdo->prepare($selectSql);
    $stmt2->execute();
    $bookings = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<br><h3>Sample Data:</h3>";
    foreach ($bookings as $booking) {
        echo "ID: {$booking['booking_id']}, Name: {$booking['passenger_name']}, Status: '{$booking['status']}'<br>";
    }
    
    echo "<br><h3>✅ Table recreated successfully!</h3>";
    echo "You can now test booking again.";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>