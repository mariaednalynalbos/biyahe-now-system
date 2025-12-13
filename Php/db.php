<?php
// Database Configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db = $_ENV['DB_NAME'] ?? 'biyahe_now';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$port = $_ENV['DB_PORT'] ?? '3306';

if (isset($_ENV['DB_HOST'])) {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
}

// PDO connection
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options); 
    $conn = $pdo;
} catch (\PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}