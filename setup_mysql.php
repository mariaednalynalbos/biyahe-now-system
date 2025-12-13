<?php
// Create MySQL database and tables for XAMPP
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS biyahe_now");
    $pdo->exec("USE biyahe_now");
    
    echo "✅ Database 'biyahe_now' created<br>";
    
    // Create accounts table
    $sql = "CREATE TABLE IF NOT EXISTS accounts (
        account_id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'driver', 'passenger') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Accounts table created<br>";
    
    // Create admins table
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Admins table created<br>";
    
    // Create drivers table
    $sql = "CREATE TABLE IF NOT EXISTS drivers (
        driver_id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        license_number VARCHAR(50),
        phone VARCHAR(20),
        status VARCHAR(20) DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Drivers table created<br>";
    
    // Create passengers table
    $sql = "CREATE TABLE IF NOT EXISTS passengers (
        passenger_id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Passengers table created<br>";
    
    // Create test accounts
    $accounts = [
        ['admin@test.com', 'admin123', 'admin'],
        ['driver@test.com', 'driver123', 'driver'],
        ['passenger@test.com', 'passenger123', 'passenger']
    ];
    
    foreach ($accounts as [$email, $password, $role]) {
        // Check if account exists
        $stmt = $pdo->prepare("SELECT account_id FROM accounts WHERE email = ?");
        $stmt->execute([$email]);
        
        if (!$stmt->fetch()) {
            // Create account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashedPassword, $role]);
            $accountId = $pdo->lastInsertId();
            
            // Create profile
            if ($role === 'admin') {
                $stmt = $pdo->prepare("INSERT INTO admins (account_id, firstname, lastname) VALUES (?, ?, ?)");
                $stmt->execute([$accountId, 'Test', 'Admin']);
            } elseif ($role === 'driver') {
                $stmt = $pdo->prepare("INSERT INTO drivers (account_id, firstname, lastname) VALUES (?, ?, ?)");
                $stmt->execute([$accountId, 'Test', 'Driver']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO passengers (account_id, firstname, lastname) VALUES (?, ?, ?)");
                $stmt->execute([$accountId, 'Test', 'Passenger']);
            }
            
            echo "✅ Created: $email / $password<br>";
        }
    }
    
    echo "<br><h3>🎉 Setup Complete!</h3>";
    echo "<p>Test accounts created:</p>";
    echo "<ul>";
    echo "<li>Admin: admin@test.com / admin123</li>";
    echo "<li>Driver: driver@test.com / driver123</li>";
    echo "<li>Passenger: passenger@test.com / passenger123</li>";
    echo "</ul>";
    
    echo "<br><a href='index.html' style='background:#007bff;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Test Login</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>