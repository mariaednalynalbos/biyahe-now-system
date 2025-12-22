<?php
header('Content-Type: application/json');
include "supabase_db.php";

try {
    $users = supabaseQuery('users', 'GET');
    
    $formatted_users = array_map(function($user) {
        return [
            'id' => $user['id'] ?? 'N/A',
            'name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
            'email' => $user['email'] ?? 'N/A',
            'type' => $user['user_type'] ?? 'N/A',
            'contact' => $user['contact_number'] ?? 'N/A',
            'created' => $user['created_at'] ?? 'N/A'
        ];
    }, $users);
    
    echo json_encode([
        'success' => true,
        'total_users' => count($users),
        'users' => $formatted_users
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>