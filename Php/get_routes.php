<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT route_id, route_name, origin, destination FROM routes ORDER BY route_name ASC");
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'routes' => $routes]);
} catch (PDOException $e) {
    error_log("Fetch Routes Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Could not fetch routes.']);
}
