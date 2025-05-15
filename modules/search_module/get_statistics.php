<?php
require_once '../../config/db_connection.php';

// Get database connection
$pdo = require '../../config/db_connection.php';

// Get filter parameters from POST data
$data = json_decode(file_get_contents('php://input'), true);
$year = isset($data['year']) ? $data['year'] : '';
$party = isset($data['party']) ? $data['party'] : '';
$position = isset($data['position']) ? $data['position'] : '';

// Build base query conditions
$conditions = ["d.status = 'Approved'"];
$params = [];

if (!empty($year)) {
    $conditions[] = "sp.year = ?";
    $params[] = $year;
}

if (!empty($party)) {
    $conditions[] = "p.name = ?";
    $params[] = $party;
}

if (!empty($position)) {
    $conditions[] = "pd.office = ?";
    $params[] = $position;
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get party statistics
$partyQuery = "SELECT p.name as party_name, COUNT(*) as count 
               FROM declarations d 
               INNER JOIN personal_data pd ON d.id = pd.declaration_id 
               INNER JOIN parties p ON pd.party_id = p.id 
               INNER JOIN submission_periods sp ON d.submission_period_id = sp.id 
               $whereClause
               GROUP BY p.name 
               ORDER BY count DESC";
$stmt = $pdo->prepare($partyQuery);
$stmt->execute($params);
$partyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get value distribution statistics
$valueQuery = "SELECT 
    CASE 
        WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 10000 THEN 'Λιγότερο από €10,000'
        WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 50000 THEN '€10,000 - €50,000'
        WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 100000 THEN '€50,000 - €100,000'
        ELSE 'Περισσότερο από €100,000'
    END as value_range,
    COUNT(*) as count
    FROM liquid_assets la
    INNER JOIN declarations d ON la.declaration_id = d.id
    INNER JOIN personal_data pd ON d.id = pd.declaration_id 
    INNER JOIN parties p ON pd.party_id = p.id 
    INNER JOIN submission_periods sp ON d.submission_period_id = sp.id 
    $whereClause
    GROUP BY value_range
    ORDER BY 
        CASE value_range
            WHEN 'Λιγότερο από €10,000' THEN 1
            WHEN '€10,000 - €50,000' THEN 2
            WHEN '€50,000 - €100,000' THEN 3
            ELSE 4
        END";
$stmt = $pdo->prepare($valueQuery);
$stmt->execute($params);
$valueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get position statistics
$positionQuery = "SELECT pd.office, COUNT(*) as count 
                 FROM declarations d 
                 INNER JOIN personal_data pd ON d.id = pd.declaration_id 
                 INNER JOIN parties p ON pd.party_id = p.id 
                 INNER JOIN submission_periods sp ON d.submission_period_id = sp.id 
                 $whereClause
                 GROUP BY pd.office 
                 ORDER BY count DESC";
$stmt = $pdo->prepare($positionQuery);
$stmt->execute($params);
$positionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get year statistics
$yearQuery = "SELECT sp.year, COUNT(*) as count 
              FROM declarations d 
              INNER JOIN personal_data pd ON d.id = pd.declaration_id 
              INNER JOIN parties p ON pd.party_id = p.id 
              INNER JOIN submission_periods sp ON d.submission_period_id = sp.id 
              $whereClause
              GROUP BY sp.year 
              ORDER BY sp.year";
$stmt = $pdo->prepare($yearQuery);
$stmt->execute($params);
$yearData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data for charts
$response = [
    'partyData' => [
        'labels' => array_column($partyData, 'party_name'),
        'values' => array_column($partyData, 'count')
    ],
    'valueData' => [
        'labels' => array_column($valueData, 'value_range'),
        'values' => array_column($valueData, 'count')
    ],
    'positionData' => [
        'labels' => array_column($positionData, 'office'),
        'values' => array_column($positionData, 'count')
    ],
    'yearData' => [
        'labels' => array_column($yearData, 'year'),
        'values' => array_column($yearData, 'count')
    ]
];

// Send response
header('Content-Type: application/json');
echo json_encode($response);
?> 