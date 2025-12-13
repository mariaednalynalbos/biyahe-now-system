<?php
include "Php/supabase_db.php";

try {
    $email = 'mariaednalynalbos@gmail.com';
    
    // 1. Delete existing user with this email
    echo "Deleting existing user...<br>";
    $existing = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
    
    if (!empty($existing)) {
        $user_id = $existing[0]['id'];
        $delete_url = 'https://pjeebuszbfcgkgtfzhdg.supabase.co/rest/v1/users?id=eq.' . $user_id;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $delete_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBqZWVidXN6YmZjZ2tndGZ6aGRnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjU1OTUxMTMsImV4cCI6MjA4MTE3MTExM30.hyDYcJ-r81gL50HIRm4k1ej_HcoGDFfm5hbFp9567is',
            'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBqZWVidXN6YmZjZ2tndGZ6aGRnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjU1OTUxMTMsImV4cCI6MjA4MTE3MTExM30.hyDYcJ-r81gL50HIRm4k1ej_HcoGDFfm5hbFp9567is'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Delete response code: " . $httpCode . "<br>";
    }
    
    // 2. Add as admin
    echo "Creating admin account...<br>";
    $admin_data = [
        'name' => 'Maria Ednalyn Albos',
        'email' => $email,
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'user_type' => 'admin',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $result = supabaseQuery('users', 'POST', $admin_data);
    echo "Admin account created successfully!<br>";
    echo "Email: " . $email . "<br>";
    echo "Password: admin123<br>";
    echo "Role: admin<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>