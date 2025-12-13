<?php
include "Php/db.php";

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    echo "✅ Database connected successfully<br><br>";
    
    // Check accounts table
    echo "<h3>Accounts Table:</h3>";
    $stmt = $conn->query("SELECT account_id, email, role FROM accounts LIMIT 10");
    $accounts = $stmt->fetchAll();
    
    if (empty($accounts)) {
        echo "❌ No accounts found in database<br>";
    } else {
        echo "✅ Found " . count($accounts) . " accounts:<br>";
        foreach ($accounts as $account) {
            echo "- ID: {$account['account_id']}, Email: {$account['email']}, Role: {$account['role']}<br>";
        }
    }
    
    echo "<br><h3>Drivers Table:</h3>";
    $stmt = $conn->query("SELECT driver_id, account_id, firstname, lastname FROM drivers LIMIT 10");
    $drivers = $stmt->fetchAll();
    
    if (empty($drivers)) {
        echo "❌ No drivers found<br>";
    } else {
        echo "✅ Found " . count($drivers) . " drivers:<br>";
        foreach ($drivers as $driver) {
            echo "- Driver ID: {$driver['driver_id']}, Account ID: {$driver['account_id']}, Name: {$driver['firstname']} {$driver['lastname']}<br>";
        }
    }
    
    echo "<br><h3>Passengers Table:</h3>";
    $stmt = $conn->query("SELECT passenger_id, account_id, firstname, lastname FROM passengers LIMIT 10");
    $passengers = $stmt->fetchAll();
    
    if (empty($passengers)) {
        echo "❌ No passengers found<br>";
    } else {
        echo "✅ Found " . count($passengers) . " passengers:<br>";
        foreach ($passengers as $passenger) {
            echo "- Passenger ID: {$passenger['passenger_id']}, Account ID: {$passenger['account_id']}, Name: {$passenger['firstname']} {$passenger['lastname']}<br>";
        }
    }
    
    echo "<br><h3>Admins Table:</h3>";
    $stmt = $conn->query("SELECT admin_id, account_id, firstname, lastname FROM admins LIMIT 10");
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        echo "❌ No admins found<br>";
    } else {
        echo "✅ Found " . count($admins) . " admins:<br>";
        foreach ($admins as $admin) {
            echo "- Admin ID: {$admin['admin_id']}, Account ID: {$admin['account_id']}, Name: {$admin['firstname']} {$admin['lastname']}<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>