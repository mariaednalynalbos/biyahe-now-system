<?php
// Supabase API connection with fallback
$supabase_url = getenv('SUPABASE_URL');
$supabase_key = getenv('SUPABASE_KEY');

// Fallback for local development
if (!$supabase_url) {
    $supabase_url = 'https://pjeebuszbfcgkgtfzhdg.supabase.co';
}
if (!$supabase_key) {
    $supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBqZWVidXN6YmZjZ2tndGZ6aGRnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjU1OTUxMTMsImV4cCI6MjA4MTE3MTExM30.hyDYcJ-r81gL50HIRm4k1ej_HcoGDFfm5hbFp9567is';
}

function supabaseQuery($table, $method = 'GET', $data = null, $filter = null) {
    global $supabase_url, $supabase_key;
    
    if (!$supabase_url || !$supabase_key) {
        throw new Exception("Supabase credentials not configured");
    }
    
    $url = $supabase_url . '/rest/v1/' . $table;
    if ($filter) $url .= '?' . $filter;
    
    $headers = [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $ch = curl_init();
    if (!$ch) {
        throw new Exception("Failed to initialize cURL");
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($data && in_array($method, ['POST', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception("cURL Error: " . $curlError);
    }
    
    if ($httpCode >= 400) {
        throw new Exception("Supabase API Error (" . $httpCode . "): " . $response);
    }
    
    return json_decode($response, true);
}
?>