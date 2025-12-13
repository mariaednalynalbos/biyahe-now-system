<?php
header('Content-Type: application/json');
include "Php/supabase_db.php";

try {
    // Test connection
    echo "Testing Supabase connection...\n";
    
    // Get all users
    $users = supabaseQuery('users', 'GET');
    
    echo "Users found: " . count($users) . "\n";
    
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . " | Email: " . $user['email'] . " | Type: " . $user['user_type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>