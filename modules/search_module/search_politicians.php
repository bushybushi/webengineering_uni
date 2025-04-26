<?php
require_once '../../config/db_connection.php';

// Get database connection
$conn = require '../../config/db_connection.php';

// Get search query
$query = $_GET['q'] ?? '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    // Search for politicians
    $stmt = $conn->prepare("
        SELECT id, name 
        FROM people 
        WHERE name LIKE ? 
        ORDER BY name 
        LIMIT 10
    ");
    $stmt->execute(['%' . $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 