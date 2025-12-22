<?php
// Alternative Supabase connection with better error handling
$supabase_url = getenv('SUPABASE_URL') ?: 'https://pjeebuszbfcgkgtfzhdg.supabase.co';
$supabase_key = getenv('SUPABASE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBqZWVidXN6YmZjZ2tndGZ6aGRnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjU1OTUxMTMsImV4cCI6MjA4MTE3MTExM30.hyDYcJ-r81gL50HIRm4k1ej_HcoGDFfm5hbFp9567is';

function supabaseQueryAlt($table, $method = 'GET', $data = null, $filter = null) {
    global $supabase_url, $supabase_key;
    
    $url = $supabase_url . '/rest/v1/' . $table;
    if ($filter) $url .= '?' . $filter;
    
    $headers = [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    // Try different cURL configurations
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification
        CURLOPT_SSL_VERIFYHOST => false,  // Disable host verification
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_DNS_CACHE_TIMEOUT => 0,   // Disable DNS cache
        CURLOPT_FRESH_CONNECT => true,    // Force new connection
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; BiyaheSystem/1.0)',
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
    ]);
    
    if ($data && in_array($method, ['POST', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Connection Error: " . $error);
    }
    
    if ($httpCode >= 400) {
        throw new Exception("API Error ($httpCode): " . $response);
    }
    
    return json_decode($response, true);
}

// Test function
function testSupabaseConnection() {
    try {
        $result = supabaseQueryAlt('users', 'GET', null, 'limit=1');
        return ['success' => true, 'message' => 'Connection successful', 'count' => count($result)];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>