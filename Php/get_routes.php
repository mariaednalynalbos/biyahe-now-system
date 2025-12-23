<?php
header('Content-Type: application/json');

// Sample routes data
$routes = [
    [
        'id' => 1,
        'name' => 'Naval to Tacloban',
        'origin' => 'Naval',
        'destination' => 'Tacloban',
        'price' => 200,
        'times' => ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00']
    ],
    [
        'id' => 2,
        'name' => 'Naval to Ormoc',
        'origin' => 'Naval',
        'destination' => 'Ormoc',
        'price' => 200,
        'times' => ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00']
    ],
    [
        'id' => 3,
        'name' => 'Naval to Lemon',
        'origin' => 'Naval',
        'destination' => 'Lemon',
        'price' => 150,
        'times' => ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00']
    ]
];

echo json_encode(['success' => true, 'routes' => $routes]);
?>