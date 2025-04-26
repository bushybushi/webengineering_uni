<?php
require_once '../../config/db_connection.php';

// Get database connection
$conn = require '../../config/db_connection.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];

// Debug log
error_log('Received IDs: ' . print_r($ids, true));

if (empty($ids)) {
    echo json_encode([]);
    exit;
}

try {
    $result = [];
    
    // Get basic info and financial data for each politician
    foreach ($ids as $id) {
        // Get person's name
        $stmt = $conn->prepare("SELECT name FROM people WHERE id = ?");
        $stmt->execute([$id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($person) {
            // Initialize values
            $realEstate = 0;
            $stocks = 0;
            $deposits = 0;

            // Check if real_estate table exists
            $stmt = $conn->query("SHOW TABLES LIKE 'real_estate'");
            if ($stmt->rowCount() > 0) {
                // Get real estate value
                $stmt = $conn->prepare("
                    SELECT value 
                    FROM real_estate 
                    WHERE person_id = ?
                ");
                $stmt->execute([$id]);
                $realEstateValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($realEstateValues as $value) {
                    $cleanValue = str_replace(['€', ','], '', $value['value']);
                    $realEstate += floatval($cleanValue);
                }
                error_log("Real estate values for ID {$id}: " . print_r($realEstateValues, true));
                error_log("Total real estate: {$realEstate}");
            }

            // Check if stocks table exists
            $stmt = $conn->query("SHOW TABLES LIKE 'stocks'");
            if ($stmt->rowCount() > 0) {
                // Get stocks value
                $stmt = $conn->prepare("
                    SELECT value 
                    FROM stocks 
                    WHERE person_id = ?
                ");
                $stmt->execute([$id]);
                $stocksValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($stocksValues as $value) {
                    $cleanValue = str_replace(['€', ','], '', $value['value']);
                    $stocks += floatval($cleanValue);
                }
                error_log("Stocks values for ID {$id}: " . print_r($stocksValues, true));
                error_log("Total stocks: {$stocks}");
            }

            // Check if deposits table exists
            $stmt = $conn->query("SHOW TABLES LIKE 'deposits'");
            if ($stmt->rowCount() > 0) {
                // Get deposits value
                $stmt = $conn->prepare("
                    SELECT amount 
                    FROM deposits 
                    WHERE person_id = ?
                ");
                $stmt->execute([$id]);
                $depositsValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($depositsValues as $value) {
                    $cleanValue = str_replace(['€', ','], '', $value['amount']);
                    $deposits += floatval($cleanValue);
                }
                error_log("Deposits values for ID {$id}: " . print_r($depositsValues, true));
                error_log("Total deposits: {$deposits}");
            }

            $result[] = [
                'name' => $person['name'],
                'real_estate' => round($realEstate, 2),
                'stocks' => round($stocks, 2),
                'deposits' => round($deposits, 2)
            ];
        }
    }

    // Debug log
    error_log('Final result: ' . print_r($result, true));
    
    header('Content-Type: application/json');
    echo json_encode($result);
} catch(PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 