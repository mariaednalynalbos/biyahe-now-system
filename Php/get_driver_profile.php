<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

try {
    include "db.php";
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$response = ['success' => false, 'message' => 'Not logged in.'];

if (isset($_SESSION['account_id']) && $_SESSION['role'] === 'driver') {
    $accountId = $_SESSION['account_id'];

    // 1. Kuhanin ang profile details:
    // **GUMAMIT NG JOIN** para kunin ang email mula sa accounts table.
    // Ito ang tamang query para makuha ang LAHAT ng drivers data (d.*) at ang email (a.email).
    $sql = "SELECT d.*, a.email 
            FROM drivers d
            JOIN accounts a ON d.account_id = a.account_id
            WHERE d.account_id = ?";
    
    // Ang nakaraang query na "SELECT * FROM drivers WHERE account_id = ?" ay HINDI na kailangan at dapat alisin.
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $profileData = $result->fetch_assoc();
            // Add default profile picture if none exists
            if (empty($profileData['profile_picture'])) {
                $profileData['profile_picture'] = 'https://via.placeholder.com/80';
            }
            $response = [
                'success' => true,
                'data' => $profileData 
            ];
        } else {
            $response['message'] = 'Driver profile not found.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Database error: ' . $conn->error;
    }
} else {
    $response['message'] = 'Access denied or invalid session.';
}

if (isset($conn)) {
    $conn->close();
}

echo json_encode($response);
?>