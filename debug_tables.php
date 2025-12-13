<?php
include "Php/db.php";

echo "<h2>🔍 Database Debug</h2>";

try {
    // Check accounts
    $stmt = $conn->query("SELECT account_id, email, role FROM accounts LIMIT 5");
    $accounts = $stmt->fetchAll();
    
    echo "<h3>Accounts (" . count($accounts) . "):</h3>";
    foreach ($accounts as $acc) {
        echo "ID: {$acc['account_id']}, Email: {$acc['email']}, Role: {$acc['role']}<br>";
    }
    
    // Check if passwords are hashed
    $stmt = $conn->query("SELECT email, LENGTH(password) as pass_len FROM accounts LIMIT 3");
    $passCheck = $stmt->fetchAll();
    
    echo "<br><h3>Password Check:</h3>";
    foreach ($passCheck as $p) {
        $status = ($p['pass_len'] > 50) ? "Hashed ✅" : "Plain Text ❌";
        echo "{$p['email']}: $status (Length: {$p['pass_len']})<br>";
    }
    
    if (empty($accounts)) {
        echo "<br>❌ No accounts found. Please register first!";
    } else {
        echo "<br>✅ Database has accounts. Try logging in!";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>