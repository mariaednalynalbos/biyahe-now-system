<?php
// MySQL Database Configuration for XAMPP
$host = 'localhost';
$db = 'biyahe_now';
$user = 'root';
$pass = '';
$port = '3306';

// PDO MySQL connection
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
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
?>