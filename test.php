<?php
// Quick diagnostic test for InfinityFree
echo "<h1>🔍 Biyahe System Diagnostic Test</h1>";

echo "<h2>✅ PHP is Working!</h2>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
echo "<h2>🗄️ Database Connection Test</h2>";
try {
    $host = 'sql100.infinityfree.com';
    $db = 'if0_40484138_biyahe_now';
    $user = 'if0_40484138';
    $pass = '9S8AKN0VMKFps';
    
    $conn = mysqli_connect($host, $user, $pass, $db);
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test if tables exist
        $tables = ['users', 'bookings', 'drivers', 'routes'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<p style='color: green;'>✅ Table '$table' exists</p>";
            } else {
                echo "<p style='color: red;'>❌ Table '$table' missing</p>";
            }
        }
        
        mysqli_close($conn);
    } else {
        echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test file structure
echo "<h2>📁 File Structure Test</h2>";
$files = [
    'index.html',
    'styles/styles.css',
    'scripts/scripts.js',
    'Php/login_backend.php',
    'Php/register_backend.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='index.html'>Go to Homepage</a></p>";
echo "<p><a href='Php/Passenger-dashboard.php'>Test Passenger Dashboard</a></p>";

echo "<h2>📋 Next Steps</h2>";
echo "<p>1. If files are missing, re-upload them to InfinityFree</p>";
echo "<p>2. If database tables are missing, import your SQL file</p>";
echo "<p>3. Check file permissions on InfinityFree</p>";
?>