<?php
include "Php/supabase_db.php";

try {
    $email = 'mariaednalynalbos@gmail.com';
    
    echo "Deleting user: " . $email . "<br>";
    
    // Get existing user
    $existing = supabaseQuery('users', 'GET', null, 'email=eq.' . urlencode($email));
    
    if (!empty($existing)) {
        $user_id = $existing[0]['id'];
        echo "Found user ID: " . $user_id . "<br>";
        
        // Delete user
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
        
        if ($httpCode == 204) {
            echo "✅ User deleted successfully!<br>";
            echo "You can now register " . $email . " as admin with your own password.<br>";
        } else {
            echo "❌ Delete failed. HTTP Code: " . $httpCode . "<br>";
            echo "Response: " . $response . "<br>";
        }
    } else {
        echo "❌ User not found in database.<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>