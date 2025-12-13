<?php
// Database Configuration
if (isset($_ENV['DATABASE_URL'])) {
    // Production (Render)
    $url = parse_url($_ENV['DATABASE_URL']);
    $host = $url['host'];
    $db = ltrim($url['path'], '/');
    $user = $url['user'];
    $pass = $url['pass'];
    $port = $url['port'];
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
} else {
    // Local development
    $host = 'localhost';
    $db = 'biyahe_now';
    $user = 'root';
    $pass = '';
    $port = '3306';
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