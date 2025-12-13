<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...<br>";

// Test with environment variables first
$host = $_ENV['DATABASE_HOST'] ?? 'localhost';
$db = $_ENV['DATABASE_NAME'] ?? 'biyahe_now';
$user = $_ENV['DATABASE_USER'] ?? 'postgres';
$pass = $_ENV['DATABASE_PASSWORD'] ?? '';
$port = $_ENV['DATABASE_PORT'] ?? '5432';

echo "Host: $host<br>";
echo "Database: $db<br>";
echo "User: $user<br>";
echo "Port: $port<br>";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass);
    echo "✅ Database connection successful!<br>";
    
    // Test if tables exist
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables found: " . implode(', ', $tables) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}
?>