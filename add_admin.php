<?php
include "Php/supabase_db.php";

// Add admin account
$admin_data = [
    'name' => 'Maria Ednalyn Albos',
    'email' => 'mariaednalynalbos@gmail.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT), // Change this password
    'user_type' => 'admin',
    'created_at' => date('Y-m-d H:i:s')
];

try {
    $result = supabaseQuery('users', 'POST', $admin_data);
    echo "Admin account created successfully!";
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>