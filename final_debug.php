<?php
include "Php/db.php";

echo "<h2>🔍 Final Database Check</h2>";

try {
    // Check accounts
    $stmt = $conn->query("SELECT account_id, email, role, LENGTH(password) as pass_len FROM accounts");
    $accounts = $stmt->fetchAll();
    
    echo "<h3>Accounts Found: " . count($accounts) . "</h3>";
    
    if (empty($accounts)) {
        echo "❌ NO ACCOUNTS FOUND! You need to register first.<br>";
        echo "<a href='index.html'>Go Register</a>";
    } else {
        foreach ($accounts as $acc) {
            $passStatus = ($acc['pass_len'] > 50) ? "Hashed ✅" : "Plain ❌";
            echo "• {$acc['email']} ({$acc['role']}) - Password: $passStatus<br>";
        }
        
        echo "<br><h3>✅ Try Login Now!</h3>";
        echo "<a href='index.html' style='background:#007bff;color:white;padding:10px;text-decoration:none;'>Login Page</a>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>