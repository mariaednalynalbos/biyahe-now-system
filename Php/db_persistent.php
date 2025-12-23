<?php
// Database configuration for persistent storage
$host = getenv('DB_HOST') ?: 'dpg-ctq4ub5umphs73e3rr50-a.oregon-postgres.render.com';
$dbname = getenv('DB_NAME') ?: 'biyahe_db';
$username = getenv('DB_USER') ?: 'biyahe_db_user';
$password = getenv('DB_PASSWORD') ?: 'LQvSXzKbWbGjF8h9vYxQGqP4mHnJ2Rcd';
$port = getenv('DB_PORT') ?: '5432';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Create users table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            name VARCHAR(200) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            user_type VARCHAR(50) DEFAULT 'passenger',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create bookings table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id),
            passenger_name VARCHAR(200) NOT NULL,
            route VARCHAR(100) NOT NULL,
            trip_time VARCHAR(20) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            seat_number VARCHAR(10) NOT NULL,
            booking_date DATE NOT NULL,
            booking_time TIME NOT NULL,
            status VARCHAR(50) DEFAULT 'Confirmed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}
?>