<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pingFile = __DIR__ . '/ping.json';

// POST: write a new ping
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['x']) || !isset($data['y'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing x or y coordinates']);
        exit;
    }

    $ping = [
        'x'       => (float)$data['x'],
        'y'       => (float)$data['y'],
        'expires' => time() + 15
    ];

    file_put_contents($pingFile, json_encode($ping), LOCK_EX);
    echo json_encode(['success' => true]);
    exit;
}

// GET: return ping if still active, null if expired or missing
if (file_exists($pingFile)) {
    $ping = json_decode(file_get_contents($pingFile), true);
    if ($ping && time() < $ping['expires']) {
        echo json_encode($ping);
        exit;
    }
}

echo json_encode(null);
?>