<?php
// DNS Checker for Philippines
header('Content-Type: application/json');

// Philippine DNS Servers
$philippine_dns = [
    'PLDT' => [
        'primary' => '202.90.136.10',
        'secondary' => '202.90.136.11'
    ],
    'Globe' => [
        'primary' => '203.177.12.4',
        'secondary' => '203.177.12.5'
    ],
    'Smart' => [
        'primary' => '202.90.136.10',
        'secondary' => '202.90.136.11'
    ],
    'Converge' => [
        'primary' => '210.213.99.99',
        'secondary' => '210.213.99.100'
    ],
    'Sky Broadband' => [
        'primary' => '203.177.12.4',
        'secondary' => '203.177.12.5'
    ]
];

// International DNS for comparison
$international_dns = [
    'Google' => [
        'primary' => '8.8.8.8',
        'secondary' => '8.8.4.4'
    ],
    'Cloudflare' => [
        'primary' => '1.1.1.1',
        'secondary' => '1.0.0.1'
    ],
    'OpenDNS' => [
        'primary' => '208.67.222.222',
        'secondary' => '208.67.220.220'
    ]
];

function checkDNSResponse($dns_server, $domain = 'google.com') {
    $start_time = microtime(true);
    
    // Use nslookup command to test DNS
    $command = "nslookup $domain $dns_server 2>&1";
    $output = shell_exec($command);
    
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2); // Convert to milliseconds
    
    $status = (strpos($output, 'Name:') !== false) ? 'Online' : 'Offline';
    
    return [
        'status' => $status,
        'response_time' => $response_time . 'ms',
        'server' => $dns_server
    ];
}

$action = $_GET['action'] ?? 'check_all';
$results = [];

if ($action === 'check_philippine') {
    foreach ($philippine_dns as $provider => $servers) {
        $results[$provider] = [
            'primary' => checkDNSResponse($servers['primary']),
            'secondary' => checkDNSResponse($servers['secondary'])
        ];
    }
} elseif ($action === 'check_international') {
    foreach ($international_dns as $provider => $servers) {
        $results[$provider] = [
            'primary' => checkDNSResponse($servers['primary']),
            'secondary' => checkDNSResponse($servers['secondary'])
        ];
    }
} else {
    // Check all DNS servers
    $results['Philippine'] = [];
    foreach ($philippine_dns as $provider => $servers) {
        $results['Philippine'][$provider] = [
            'primary' => checkDNSResponse($servers['primary']),
            'secondary' => checkDNSResponse($servers['secondary'])
        ];
    }
    
    $results['International'] = [];
    foreach ($international_dns as $provider => $servers) {
        $results['International'][$provider] = [
            'primary' => checkDNSResponse($servers['primary']),
            'secondary' => checkDNSResponse($servers['secondary'])
        ];
    }
}

echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results
]);
?>