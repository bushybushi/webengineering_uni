<?php
require_once '../../config/db_connection.php';

// Get database connection
$conn = require '../../config/db_connection.php';

// Get person IDs from request
$person1_id = isset($_GET['person1']) ? (int)$_GET['person1'] : 0;
$person2_id = isset($_GET['person2']) ? (int)$_GET['person2'] : 0;

if (!$person1_id || !$person2_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid person IDs']);
    exit;
}

try {
    // Get person 1 details
    $stmt = $conn->prepare("
        SELECT p.*, 
               COALESCE(SUM(CAST(REPLACE(REPLACE(la.amount, '€', ''), ',', '') AS DECIMAL(10,2))), 0) as total_assets
        FROM people p
        LEFT JOIN liquid_assets la ON p.id = la.person_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$person1_id]);
    $person1 = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get person 2 details
    $stmt->execute([$person2_id]);
    $person2 = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$person1 || !$person2) {
        http_response_code(404);
        echo json_encode(['error' => 'One or both persons not found']);
        exit;
    }

    // Format the response
    $response = [
        'person1' => [
            'name' => $person1['name'],
            'office' => $person1['office'],
            'political_affiliation' => $person1['political_affiliation'],
            'total_assets' => '€' . number_format($person1['total_assets'], 2),
            'date_of_submission' => date('d/m/Y', strtotime($person1['date_of_submission']))
        ],
        'person2' => [
            'name' => $person2['name'],
            'office' => $person2['office'],
            'political_affiliation' => $person2['political_affiliation'],
            'total_assets' => '€' . number_format($person2['total_assets'], 2),
            'date_of_submission' => date('d/m/Y', strtotime($person2['date_of_submission']))
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 