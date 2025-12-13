<?php
// Database Configuration
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'biyahe_now';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

if (getenv('DB_HOST')) {
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