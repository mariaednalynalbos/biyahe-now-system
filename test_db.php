<?php
header('Content-Type: application/json');

echo "<h3>Testing database connection...</h3>";
echo "Host: " . (getenv('DB_HOST') ?: 'localhost') . "<br>";
echo "Database: " . (getenv('DB_NAME') ?: 'biyahe_now') . "<br>";
echo "User: " . (getenv('DB_USER') ?: 'root') . "<br>";
echo "Port: " . (getenv('DB_PORT') ?: '3306') . "<br>";

try {
    include "Php/db.php";
    
    // Test connection
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    echo "✅ Database connected successfully<br>";
    echo "Test result: " . json_encode($result);
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>