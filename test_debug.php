<?php
// Debug test for login
$email = 'mariaednalynalbos@gmail.com';
$password = 'password123456789';

echo "Testing login with:\n";
echo "Email: $email\n";
echo "Password: $password\n\n";

// Simulate the login logic
$admin_accounts = [
    'mariaednalynalbos@gmail.com' => [
        'password' => 'password123456789',
        'name' => 'Maria Ednalyn Albos',
        'role' => 'admin'
    ]
];

if (isset($admin_accounts[$email])) {
    echo "Admin account found!\n";
    $account = $admin_accounts[$email];
    if ($password === $account['password']) {
        echo "Password matches!\n";
        echo "Login should be successful\n";
    } else {
        echo "Password does NOT match\n";
        echo "Expected: " . $account['password'] . "\n";
        echo "Got: $password\n";
    }
} else {
    echo "Admin account NOT found\n";
}
?>