<?php
header('Content-Type: text/plain');

echo "=== LOGIN TEST ===\n\n";

$test_email = 'mariaednalynalbos@gmail.com';
$test_password = 'password123456789';

echo "Testing credentials:\n";
echo "Email: " . $test_email . "\n";
echo "Password: " . $test_password . "\n\n";

// File-based admin accounts (same as in login_reliable.php)
$admin_accounts = [
    'mariaednalynalbos@gmail.com' => [
        'password' => 'password123456789',
        'name' => 'Maria Ednalyn Albos',
        'role' => 'admin'
    ],
    'admin@biyahe.com' => [
        'password' => 'admin123',
        'name' => 'System Admin',
        'role' => 'admin'
    ]
];

echo "Available accounts:\n";
foreach ($admin_accounts as $email => $account) {
    echo "- " . $email . " (password: " . $account['password'] . ")\n";
}

echo "\nTesting login logic:\n";

if (isset($admin_accounts[$test_email])) {
    echo "✅ Email found in accounts\n";
    $account = $admin_accounts[$test_email];
    
    if ($test_password === $account['password']) {
        echo "✅ Password matches\n";
        echo "✅ LOGIN SHOULD WORK!\n";
        echo "Role: " . $account['role'] . "\n";
        echo "Name: " . $account['name'] . "\n";
    } else {
        echo "❌ Password mismatch\n";
        echo "Expected: " . $account['password'] . "\n";
        echo "Got: " . $test_password . "\n";
    }
} else {
    echo "❌ Email not found in accounts\n";
}

echo "\n=== END TEST ===\n";
?>