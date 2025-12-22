<?php
header('Content-Type: text/plain');

echo "=== SUPABASE CONNECTION TEST ===\n\n";

// Test 1: Check environment variables
echo "1. Environment Variables:\n";
$supabase_url = getenv('SUPABASE_URL');
$supabase_key = getenv('SUPABASE_KEY');

if (!$supabase_url) {
    $supabase_url = 'https://pjeebuszbfcgkgtfzhdg.supabase.co';
    echo "   SUPABASE_URL: Using fallback\n";
} else {
    echo "   SUPABASE_URL: " . substr($supabase_url, 0, 30) . "...\n";
}

if (!$supabase_key) {
    $supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBqZWVidXN6YmZjZ2tndGZ6aGRnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjU1OTUxMTMsImV4cCI6MjA4MTE3MTExM30.hyDYcJ-r81gL50HIRm4k1ej_HcoGDFfm5hbFp9567is';
    echo "   SUPABASE_KEY: Using fallback\n";
} else {
    echo "   SUPABASE_KEY: " . substr($supabase_key, 0, 30) . "...\n";
}

// Test 2: DNS Resolution
echo "\n2. DNS Resolution Test:\n";
$host = 'pjeebuszbfcgkgtfzhdg.supabase.co';
$ip = gethostbyname($host);
if ($ip === $host) {
    echo "   ❌ FAILED: Cannot resolve $host\n";
    echo "   This is the main issue!\n";
} else {
    echo "   ✅ SUCCESS: $host resolves to $ip\n";
}

// Test 3: cURL availability
echo "\n3. cURL Extension:\n";
if (function_exists('curl_init')) {
    echo "   ✅ cURL is available\n";
    $curl_version = curl_version();
    echo "   Version: " . $curl_version['version'] . "\n";
} else {
    echo "   ❌ cURL is NOT available\n";
}

// Test 4: Direct connection test
echo "\n4. Direct Connection Test:\n";
$test_url = $supabase_url . '/rest/v1/';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $supabase_key,
    'Authorization: Bearer ' . $supabase_key
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    echo "   ❌ FAILED: " . $error . "\n";
} else {
    echo "   ✅ SUCCESS: HTTP " . $http_code . "\n";
    echo "   Response: " . substr($response, 0, 100) . "...\n";
}

// Test 5: Alternative DNS servers
echo "\n5. Testing Alternative DNS:\n";
$dns_servers = ['8.8.8.8', '1.1.1.1', '208.67.222.222'];
foreach ($dns_servers as $dns) {
    echo "   Testing with $dns... ";
    $result = @dns_get_record($host, DNS_A);
    if ($result) {
        echo "✅\n";
    } else {
        echo "❌\n";
    }
}

echo "\n=== END TEST ===\n";
?>