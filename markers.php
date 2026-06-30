<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$markersFile = __DIR__ . '/markers.json';

function loadMarkers($file) {
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveMarkers($file, $markers) {
    file_put_contents($file, json_encode(array_values($markers), JSON_PRETTY_PRINT), LOCK_EX);
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: return all markers
if ($method === 'GET') {
    echo json_encode(loadMarkers($markersFile));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// POST: add a new marker
if ($method === 'POST') {
    $required = ['type', 'label', 'x', 'y'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $field"]);
            exit;
        }
    }

    $validTypes = ['vehicle', 'plane', 'bicycle', 'tractor', 'fuel', 'food', 'water', 'power', 'ammunition', 'weapon', 'danger', 'general'];
    if (!in_array($input['type'], $validTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid marker type']);
        exit;
    }

    $marker = [
        'id'      => uniqid('m_', true),
        'type'    => $input['type'],
        'label'   => substr(trim($input['label']), 0, 64),
        'x'       => (float)$input['x'],
        'y'       => (float)$input['y'],
        'created' => time()
    ];

    $markers = loadMarkers($markersFile);
    $markers[] = $marker;
    saveMarkers($markersFile, $markers);

    echo json_encode(['success' => true, 'marker' => $marker]);
    exit;
}

// DELETE: remove a marker by id
if ($method === 'DELETE') {
    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing marker id']);
        exit;
    }

    $markers = loadMarkers($markersFile);
    $filtered = array_filter($markers, fn($m) => $m['id'] !== $input['id']);

    if (count($filtered) === count($markers)) {
        http_response_code(404);
        echo json_encode(['error' => 'Marker not found']);
        exit;
    }

    saveMarkers($markersFile, $filtered);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>
