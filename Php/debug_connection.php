<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    include "supabase_db.php";
    
    // Test basic connection
    $result = supabaseQuery('users', 'GET', null, 'limit=1');
    
    echo json_encode([
        'success' => true,
        'message' => 'Connection successful',
        'supabase_url' => $supabase_url ? 'Set' : 'Not set',
        'supabase_key' => $supabase_key ? 'Set' : 'Not set',
        'users_count' => count($result)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => __FILE__,
        'line' => __LINE__
    ]);
}
?>