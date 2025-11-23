<?php
// Render PostgreSQL Database Configuration
$host = $_ENV['DATABASE_HOST'] ?? 'localhost';
$db = $_ENV['DATABASE_NAME'] ?? 'biyahe_now';
$user = $_ENV['DATABASE_USER'] ?? 'postgres';
$pass = $_ENV['DATABASE_PASSWORD'] ?? '';
$port = $_ENV['DATABASE_PORT'] ?? '5432';

// PDO PostgreSQL connection
$charset = 'utf8';
$dsn = "pgsql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options); 
    // For compatibility, create a mysqli-like connection object
    $conn = $pdo;
} catch (\PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die("Database connection failed");
}
?>