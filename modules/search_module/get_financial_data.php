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
    
    foreach ($ids as $id) {
        error_log("Processing ID: " . $id);
        
        // Get person's name
        $stmt = $conn->prepare("
            SELECT d.id, pd.full_name as name 
            FROM declarations d 
            INNER JOIN personal_data pd ON d.id = pd.declaration_id 
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Person data: " . print_r($person, true));
        
        if ($person) {
            // Get real estate value
            $stmt = $conn->prepare("
                SELECT SUM(
                    CASE 
                        WHEN current_value IS NULL OR current_value = '' THEN 0
                        WHEN current_value REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(current_value AS DECIMAL(10,2))
                        ELSE 0
                    END
                ) as total
                FROM properties 
                WHERE declaration_id = ?
            ");
            $stmt->execute([$id]);
            $realEstate = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            error_log("Real estate query result: " . print_r($realEstate, true));

            // Get stocks value
            $stmt = $conn->prepare("
                SELECT SUM(
                    CASE 
                        WHEN amount IS NULL OR amount = '' THEN 0
                        WHEN amount REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(amount AS DECIMAL(10,2))
                        ELSE 0
                    END
                ) as total
                FROM liquid_assets 
                WHERE declaration_id = ? AND type = 'stocks'
            ");
            $stmt->execute([$id]);
            $stocks = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            error_log("Stocks query result: " . print_r($stocks, true));

            // Get deposits value
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM deposits 
                WHERE declaration_id = ?
            ");
            $stmt->execute([$id]);
            $deposits = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            error_log("Deposits query result: " . print_r($deposits, true));

            $result[] = [
                'name' => $person['name'],
                'real_estate' => round($realEstate, 2),
                'stocks' => round($stocks, 2),
                'deposits' => round($deposits, 2)
            ];
            
            error_log("Added to result: " . print_r(end($result), true));
        } else {
            error_log("No person found for ID: " . $id);
        }
    }

    error_log('Final result array: ' . print_r($result, true));
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch(PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    error_log('Error trace: ' . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> <?php
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
        $stmt = $conn->prepare("
            SELECT pd.full_name as name 
            FROM declarations d 
            INNER JOIN personal_data pd ON d.id = pd.declaration_id 
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($person) {
            // Initialize values
            $realEstate = 0;
            $stocks = 0;
            $deposits = 0;

            // Get real estate value
            $stmt = $conn->prepare("
                SELECT value 
                FROM properties 
                WHERE declaration_id = ?
            ");
            $stmt->execute([$id]);
            $realEstateValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($realEstateValues as $value) {
                $cleanValue = str_replace(['€', ','], '', $value['value']);
                $realEstate += floatval($cleanValue);
            }
            error_log("Real estate values for ID {$id}: " . print_r($realEstateValues, true));
            error_log("Total real estate: {$realEstate}");

            // Get stocks value from liquid_assets
            $stmt = $conn->prepare("
                SELECT amount 
                FROM liquid_assets 
                WHERE declaration_id = ? AND type = 'stocks'
            ");
            $stmt->execute([$id]);
            $stocksValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($stocksValues as $value) {
                $cleanValue = str_replace(['€', ','], '', $value['amount']);
                $stocks += floatval($cleanValue);
            }
            error_log("Stocks values for ID {$id}: " . print_r($stocksValues, true));
            error_log("Total stocks: {$stocks}");

            // Get deposits value
            $stmt = $conn->prepare("
                SELECT amount 
                FROM deposits 
                WHERE declaration_id = ?
            ");
            $stmt->execute([$id]);
            $depositsValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($depositsValues as $value) {
                $cleanValue = str_replace(['€', ','], '', $value['amount']);
                $deposits += floatval($cleanValue);
            }
            error_log("Deposits values for ID {$id}: " . print_r($depositsValues, true));
            error_log("Total deposits: {$deposits}");

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
