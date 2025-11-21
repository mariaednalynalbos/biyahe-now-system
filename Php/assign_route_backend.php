<?php
header('Content-Type: application/json');

// ASSUMPTION: Your database connection is in config.php
include_once('config.php'); 

$response = ['status' => 'error', 'message' => 'Invalid request method.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get and sanitize input data
    $driverId = $_POST['driverId'] ?? null;
    $plateNo = $_POST['plateNo'] ?? null;
    $routeId = $_POST['routeId'] ?? null; 
    $vanImage = $_POST['vanImage'] ?? null;

    if (empty($driverId) || empty($plateNo) || empty($routeId)) {
        $response['message'] = 'Driver ID, Plate Number, and Route are required.';
    } else {
        // Simple sanitization
        $driverId = $conn->real_escape_string($driverId);
        $plateNo = $conn->real_escape_string($plateNo);
        $routeId = $conn->real_escape_string($routeId);
        $vanImage = $conn->real_escape_string($vanImage);

        // 2. Update the 'drivers' table
        // We assume the 'route' column in the drivers table stores the Route ID/Name (based on your JS/HTML)
        $sql = "UPDATE drivers 
                SET plate_no = '$plateNo', 
                    route = '$routeId', 
                    van_image = '$vanImage' 
                WHERE driver_id = '$driverId'";

        if ($conn->query($sql) === TRUE) {
            $response['status'] = 'success';
            $response['message'] = "Driver ID #$driverId successfully assigned with Plate No. $plateNo and Route $routeId.";
        } else {
            $response['message'] = "Database error: " . $conn->error;
        }
    }
}

$conn->close();
echo json_encode($response);
?>