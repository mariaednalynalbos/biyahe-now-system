<?php
header('Content-Type: text/plain');
include "supabase_alt.php";

echo "=== ADMIN ACCOUNT DEBUG ===\n\n";

$email = 'mariaednalynalbos@gmail.com';
$password = 'password123456789';

try {
    // Check if account exists
    echo "1. Checking if account exists...\n";
    $users = supabaseQueryAlt('users', 'GET', null, 'email=eq.' . urlencode($email));
    
    if (empty($users)) {
        echo "   ❌ ACCOUNT NOT FOUND!\n";
        echo "   Need to create account first.\n\n";
        
        // Create the account
        echo "2. Creating admin account...\n";
        $userData = [
            'first_name' => 'Maria Ednalyn',
            'last_name' => 'Albos',
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => 'admin',
            'created_at' => date('Y-m-d\TH:i:s\Z')
        ];

        $result = supabaseQueryAlt('users', 'POST', $userData);
        
        if ($result) {
            echo "   ✅ ADMIN ACCOUNT CREATED!\n";
            echo "   Email: " . $email . "\n";
            echo "   Password: " . $password . "\n";
        } else {
            echo "   ❌ FAILED TO CREATE ACCOUNT\n";
        }
        
    } else {
        echo "   ✅ ACCOUNT EXISTS!\n";
        $user = $users[0];
        
        echo "   ID: " . $user['id'] . "\n";
        echo "   Name: " . ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '') . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Type: " . $user['user_type'] . "\n";
        echo "   Created: " . $user['created_at'] . "\n\n";
        
        // Test password
        echo "2. Testing password...\n";
        if (password_verify($password, $user['password'])) {
            echo "   ✅ PASSWORD CORRECT!\n";
        } else {
            echo "   ❌ PASSWORD INCORRECT!\n";
            echo "   Updating password...\n";
            
            // Update password
            $updateData = ['password' => password_hash($password, PASSWORD_DEFAULT)];
            $updateResult = supabaseQueryAlt('users', 'PATCH', $updateData, 'id=eq.' . $user['id']);
            
            if ($updateResult) {
                echo "   ✅ PASSWORD UPDATED!\n";
            } else {
                echo "   ❌ FAILED TO UPDATE PASSWORD\n";
            }
        }
    }
    
    echo "\n3. Login Test...\n";
    $loginUsers = supabaseQueryAlt('users', 'GET', null, 'email=eq.' . urlencode($email));
    
    if (!empty($loginUsers)) {
        $loginUser = $loginUsers[0];
        if (password_verify($password, $loginUser['password'])) {
            echo "   ✅ LOGIN SHOULD WORK NOW!\n";
            echo "   Use: " . $email . " / " . $password . "\n";
        } else {
            echo "   ❌ LOGIN STILL FAILING\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== END DEBUG ===\n";
?>