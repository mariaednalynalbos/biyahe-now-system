<?php
include "Php/db.php";

echo "<h2>🔍 Checking Your Database Tables</h2>";

try {
    // Check accounts table
    echo "<h3>Accounts Table:</h3>";
    $stmt = $conn->query("SELECT account_id, email, role FROM accounts LIMIT 10");
    $accounts = $stmt->fetchAll();
    
    if (empty($accounts)) {
        echo "❌ No accounts found<br>";
    } else {
        echo "✅ Found " . count($accounts) . " accounts:<br>";
        foreach ($accounts as $account) {
            echo "- ID: {$account['account_id']}, Email: {$account['email']}, Role: {$account['role']}<br>";
        }
    }
    
    // Check admin table
    echo "<br><h3>Admin Table:</h3>";
    $stmt = $conn->query("SELECT * FROM admin LIMIT 10");
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        echo "❌ No admins found<br>";
    } else {
        echo "✅ Found " . count($admins) . " admins<br>";
    }
    
    // Check driver table
    echo "<br><h3>Driver Table:</h3>";
    $stmt = $conn->query("SELECT * FROM driver LIMIT 10");
    $drivers = $stmt->fetchAll();
    
    if (empty($drivers)) {
        echo "❌ No drivers found<br>";
    } else {
        echo "✅ Found " . count($drivers) . " drivers<br>";
    }
    
    // Create test account if none exist
    if (empty($accounts)) {
        echo "<br><h3>🛠️ Creating Test Account...</h3>";
        
        $email = "test@admin.com";
        $password = password_hash("admin123", PASSWORD_DEFAULT);
        $role = "admin";
        
        $stmt = $conn->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $password, $role]);
        $accountId = $conn->lastInsertId();
        
        // Add to admin table
        $stmt = $conn->prepare("INSERT INTO admin (account_id, firstname, lastname) VALUES (?, ?, ?)");
        $stmt->execute([$accountId, 'Test', 'Admin']);
        
        echo "✅ Created test admin: $email / admin123<br>";
    }
    
    echo "<br><a href='index.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>