<?php
header('Content-Type: text/plain');

echo "=== LOGIN DEBUG ===\n\n";

$email = 'mariaednalynalbos@gmail.com';
$password = 'password123456789';

echo "Testing: $email / $password\n\n";

// Check if users.json exists
$usersFile = __DIR__ . '/users.json';
echo "1. Checking users.json file:\n";
if (file_exists($usersFile)) {
    echo "   ✅ File exists\n";
    $users = json_decode(file_get_contents($usersFile), true);
    echo "   Users count: " . count($users) . "\n";
    
    foreach ($users as $user) {
        echo "   - " . $user['email'] . " (" . $user['user_type'] . ")\n";
    }
} else {
    echo "   ❌ File does not exist\n";
    echo "   Creating empty file...\n";
    file_put_contents($usersFile, json_encode([]));
}

echo "\n2. Testing hardcoded admin accounts:\n";
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

if (isset($admin_accounts[$email])) {
    echo "   ✅ Email found in admin accounts\n";
    if ($password === $admin_accounts[$email]['password']) {
        echo "   ✅ Password matches\n";
        echo "   ✅ LOGIN SHOULD WORK!\n";
    } else {
        echo "   ❌ Password mismatch\n";
    }
} else {
    echo "   ❌ Email not in admin accounts\n";
}

echo "\n3. Testing POST simulation:\n";
$_POST['email'] = $email;
$_POST['password'] = $password;

// Simulate the login logic
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo "   ❌ Empty fields\n";
} else {
    $test_email = strtolower(trim($_POST['email']));
    $test_password = $_POST['password'];
    
    echo "   Email: $test_email\n";
    echo "   Password: $test_password\n";
    
    if (isset($admin_accounts[$test_email])) {
        echo "   ✅ Found in admin accounts\n";
        if ($test_password === $admin_accounts[$test_email]['password']) {
            echo "   ✅ SIMULATION SUCCESS!\n";
        } else {
            echo "   ❌ Password failed\n";
        }
    } else {
        echo "   ❌ Not found\n";
    }
}

echo "\n=== END DEBUG ===\n";
?>