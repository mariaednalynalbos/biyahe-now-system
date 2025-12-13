<?php
include "Php/supabase_db.php";

try {
    echo "<h2>Testing Supabase Connection</h2>";
    
    // Get all users
    $users = supabaseQuery('users', 'GET');
    
    echo "<h3>Total Users: " . count($users) . "</h3>";
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>User Type</th><th>Created At</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . ($user['id'] ?? 'N/A') . "</td>";
            echo "<td>" . ($user['name'] ?? 'N/A') . "</td>";
            echo "<td>" . ($user['email'] ?? 'N/A') . "</td>";
            echo "<td>" . ($user['user_type'] ?? 'N/A') . "</td>";
            echo "<td>" . ($user['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in database.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>