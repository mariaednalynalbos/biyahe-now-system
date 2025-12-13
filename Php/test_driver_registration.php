<?php
// Simple test script for driver registration
header('Content-Type: application/json');
include "supabase_db.php";

try {
    // Test data
    $testData = [
        'first_name' => 'Test',
        'last_name' => 'Driver',
        'email' => 'testdriver@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'user_type' => 'driver',
        'contact_number' => '09123456789',
        'address' => 'Test Address',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Male',
        'vehicle_type' => 'Van',
        'plate_number' => 'ABC123',
        'license_number' => 'LIC123456',
        'license_expiry_date' => '2025-12-31',
        'area_of_operation' => 'Biliran',
        'working_schedule' => '6AM-6PM',
        'years_experience' => 5,
        'status' => 'Available',
        'created_at' => date('Y-m-d\TH:i:s\Z')
    ];

    echo "Testing Supabase connection...\n";
    
    // First, try to get existing users
    $users = supabaseQuery('users', 'GET');
    echo "Current users count: " . count($users) . "\n";
    
    // Try to insert test driver
    $result = supabaseQuery('users', 'POST', $testData);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Test driver created successfully!',
            'data' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create test driver'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>