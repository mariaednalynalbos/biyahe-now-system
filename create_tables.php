<?php
include "Php/db.php";

echo "<h2>🏗️ Creating Database Tables</h2>";

try {
    // Create accounts table
    $sql = "CREATE TABLE IF NOT EXISTS accounts (
        account_id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL CHECK (role IN ('admin', 'driver', 'passenger')),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ Accounts table created<br>";

    // Create admins table
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        admin_id SERIAL PRIMARY KEY,
        account_id INTEGER REFERENCES accounts(account_id) ON DELETE CASCADE,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ Admins table created<br>";

    // Create drivers table
    $sql = "CREATE TABLE IF NOT EXISTS drivers (
        driver_id SERIAL PRIMARY KEY,
        account_id INTEGER REFERENCES accounts(account_id) ON DELETE CASCADE,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        license_number VARCHAR(50),
        phone VARCHAR(20),
        status VARCHAR(20) DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ Drivers table created<br>";

    // Create passengers table
    $sql = "CREATE TABLE IF NOT EXISTS passengers (
        passenger_id SERIAL PRIMARY KEY,
        account_id INTEGER REFERENCES accounts(account_id) ON DELETE CASCADE,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ Passengers table created<br>";

    // Create routes table
    $sql = "CREATE TABLE IF NOT EXISTS routes (
        route_id SERIAL PRIMARY KEY,
        origin VARCHAR(100) NOT NULL,
        destination VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        duration VARCHAR(50),
        distance VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✅ Routes table created<br>";

    echo "<br><h3>🎉 All tables created successfully!</h3>";
    echo "<p>Now run the <a href='fix_login_system.php'>Login System Fix</a> to create test accounts.</p>";

} catch (Exception $e) {
    echo "❌ Error creating tables: " . $e->getMessage();
}
?>