<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

$dbPath = '/home/amp/.ampdata/instances/CobbsSCUM01/scum/3792580/SCUM/Saved/SaveFiles/SCUM.db';

if (!file_exists($dbPath)) {
    echo json_encode(['error' => 'Database file not found at path.']);
    exit;
}

try {
    $db = new SQLite3($dbPath, SQLITE3_OPEN_READONLY);

    // FORCE SQLite to map and merge the live transactions out of the -wal file
    //$db->exec("PRAGMA journal_mode=WAL;");
    //$db->exec("PRAGMA synchronous=NORMAL;");

    $players = [];

    // Relational query stitching together profiles, prisoners, entities, and live locations
    $query = "
        SELECT 
            up.name AS Name, 
            e.location_x AS X, 
            e.location_y AS Y 
        FROM user_profile up
        JOIN prisoner p ON up.id = p.user_profile_id
        JOIN prisoner_entity pe ON p.id = pe.prisoner_id
        JOIN entity e ON pe.entity_id = e.id
        WHERE p.is_alive = 1
    ";

    $results = $db->query($query);
    
    if ($results === false) {
        throw new Exception("Query failed: " . $db->lastErrorMsg());
    }

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        // Debug: log every raw row before filtering
        error_log("ROW: " . json_encode($row));
        
        if (empty($row['Name']) || ($row['X'] == 0 && $row['Y'] == 0)) {
            error_log("FILTERED OUT: " . json_encode($row));
            continue;
        }
        
        $players[] = [
            'name' => $row['Name'],
            'x'    => (float)$row['X'],
            'y'    => (float)$row['Y']
        ];
    }

    echo json_encode($players);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>