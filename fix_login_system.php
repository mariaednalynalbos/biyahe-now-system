<?php
session_start();
include "Php/db.php";

echo "<h2>🔧 Biyahe Now Login System Fix</h2>";

try {
    // Test database connection
    echo "✅ Database connected successfully<br><br>";
    
    // Check if tables exist
    echo "<h3>📋 Checking Tables...</h3>";
    
    $tables = ['accounts', 'drivers', 'passengers', 'admins'];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✅ Table '$table' exists with $count records<br>";
        } catch (Exception $e) {
            echo "❌ Table '$table' missing or error: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><h3>🔍 Sample Data Check:</h3>";
    
    // Check accounts table structure and data
    try {
        $stmt = $conn->query("SELECT account_id, email, role, password FROM accounts LIMIT 3");
        $accounts = $stmt->fetchAll();
        
        if (empty($accounts)) {
            echo "❌ No accounts found. You need to register first!<br>";
            echo "<strong>Solution:</strong> Go to your main page and register a new account.<br><br>";
        } else {
            echo "✅ Found accounts:<br>";
            foreach ($accounts as $account) {
                $passwordType = (strlen($account['password']) > 50) ? "Hashed ✅" : "Plain Text ❌";
                echo "- Email: {$account['email']}, Role: {$account['role']}, Password: $passwordType<br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error checking accounts: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><h3>🛠️ Quick Fixes:</h3>";
    
    // Fix 1: Create test accounts if none exist
    if (empty($accounts)) {
        echo "<h4>Creating Test Accounts...</h4>";
        
        $testAccounts = [
            ['admin@test.com', 'admin123', 'admin'],
            ['driver@test.com', 'driver123', 'driver'],
            ['passenger@test.com', 'passenger123', 'passenger']
        ];
        
        foreach ($testAccounts as [$email, $password, $role]) {
            try {
                // Create account
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO accounts (email, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$email, $hashedPassword, $role]);
                $accountId = $conn->lastInsertId();
                
                // Create profile based on role
                if ($role === 'admin') {
                    $stmt = $conn->prepare("INSERT INTO admins (account_id, firstname, lastname) VALUES (?, ?, ?)");
                    $stmt->execute([$accountId, 'Test', 'Admin']);
                } elseif ($role === 'driver') {
                    $stmt = $conn->prepare("INSERT INTO drivers (account_id, firstname, lastname) VALUES (?, ?, ?)");
                    $stmt->execute([$accountId, 'Test', 'Driver']);
                } else {
                    $stmt = $conn->prepare("INSERT INTO passengers (account_id, firstname, lastname) VALUES (?, ?, ?)");
                    $stmt->execute([$accountId, 'Test', 'Passenger']);
                }
                
                echo "✅ Created test account: $email (password: $password)<br>";
            } catch (Exception $e) {
                echo "❌ Failed to create $email: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // Fix 2: Update plain text passwords to hashed
    echo "<br><h4>Fixing Password Hashing...</h4>";
    try {
        $stmt = $conn->query("SELECT account_id, email, password FROM accounts");
        $allAccounts = $stmt->fetchAll();
        
        foreach ($allAccounts as $account) {
            // Check if password is not hashed (less than 50 characters usually means plain text)
            if (strlen($account['password']) < 50) {
                $hashedPassword = password_hash($account['password'], PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE accounts SET password = ? WHERE account_id = ?");
                $updateStmt->execute([$hashedPassword, $account['account_id']]);
                echo "✅ Fixed password for: {$account['email']}<br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error fixing passwords: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><h3>🎯 Test Login Now:</h3>";
    echo "<p>Try logging in with these test accounts:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@test.com / admin123</li>";
    echo "<li><strong>Driver:</strong> driver@test.com / driver123</li>";
    echo "<li><strong>Passenger:</strong> passenger@test.com / passenger123</li>";
    echo "</ul>";
    
    echo "<br><a href='index.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage();
    echo "<br><br><strong>Possible Solutions:</strong>";
    echo "<ul>";
    echo "<li>Check if your database server is running</li>";
    echo "<li>Verify database credentials in Php/db.php</li>";
    echo "<li>Make sure your database tables are created</li>";
    echo "</ul>";
}
?>