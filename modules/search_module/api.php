<?php
require_once '../../config/db_connection.php';

// Get database connection
$pdo = require '../../config/db_connection.php';

// Function to validate API key
function validateApiKey($pdo, $apiKey) {
    if (empty($apiKey)) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT role FROM api_keys WHERE key_value = ?");
    $stmt->execute([$apiKey]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Update last used timestamp
        $updateStmt = $pdo->prepare("UPDATE api_keys SET last_used = CURRENT_TIMESTAMP WHERE key_value = ?");
        $updateStmt->execute([$apiKey]);
        return $result['role'];
    }

    return false;
}

// Get API key from header
$apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';

// Validate API key
$role = validateApiKey($pdo, $apiKey);
if (!$role) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or missing API key']);
    exit;
}

// Check permissions based on role and request method
$method = $_SERVER['REQUEST_METHOD'];
if ($role !== 'admin' && $method !== 'GET') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Only admin users can perform ' . $method . ' requests']);
    exit;
}

// Handle POST request for creating new declaration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON data from request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            throw new Exception('Invalid JSON data');
        }

        // Start transaction
        $pdo->beginTransaction();

        // Find the next available ID
        $maxIdQuery = "SELECT MAX(id) as max_id FROM declarations";
        $maxIdStmt = $pdo->query($maxIdQuery);
        $maxId = $maxIdStmt->fetch(PDO::FETCH_ASSOC)['max_id'];
        $nextId = $maxId + 1;

        // Insert into declarations table with explicit ID
        $declarationQuery = "INSERT INTO declarations (id, title, status, submission_date) VALUES (?, ?, 'Pending', NOW())";
        $declarationStmt = $pdo->prepare($declarationQuery);
        $declarationStmt->execute([$nextId, $data['title']]);
        $declarationId = $nextId;

        // Insert personal data
        $personalDataQuery = "INSERT INTO personal_data (declaration_id, full_name, office, address, dob, id_number, marital_status, dependants, party_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $personalDataStmt = $pdo->prepare($personalDataQuery);
        $personalDataStmt->execute([
            $declarationId,
            $data['personal_data']['full_name'],
            $data['personal_data']['office'],
            $data['personal_data']['address'],
            $data['personal_data']['dob'],
            $data['personal_data']['id_number'],
            $data['personal_data']['marital_status'],
            $data['personal_data']['dependants'],
            $data['personal_data']['party_id']
        ]);

        // Insert properties
        if (!empty($data['properties'])) {
            $propertyQuery = "INSERT INTO properties (declaration_id, location, type, area, topographic_data, rights_burdens, acquisition_mode, acquisition_date, acquisition_value, current_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $propertyStmt = $pdo->prepare($propertyQuery);
            foreach ($data['properties'] as $property) {
                $propertyStmt->execute([
                    $declarationId,
                    $property['location'] ?? '',
                    $property['type'] ?? '',
                    $property['area'] ?? 0,
                    $property['topographic_data'] ?? '',
                    $property['rights_burdens'] ?? '',
                    $property['acquisition_mode'] ?? '',
                    $property['acquisition_date'] ?? null,
                    $property['acquisition_value'] ?? '',
                    $property['current_value'] ?? ''
                ]);
            }
        }

        // Insert vehicles
        if (!empty($data['vehicles'])) {
            $vehicleQuery = "INSERT INTO vehicles (declaration_id, brand, manu_year, value, type) VALUES (?, ?, ?, ?, ?)";
            $vehicleStmt = $pdo->prepare($vehicleQuery);
            foreach ($data['vehicles'] as $vehicle) {
                $vehicleStmt->execute([
                    $declarationId,
                    $vehicle['brand'] ?? '',
                    $vehicle['manu_year'] ?? null,
                    $vehicle['value'] ?? 0,
                    $vehicle['type'] ?? ''
                ]);
            }
        }

        // Insert liquid assets
        if (!empty($data['liquid_assets'])) {
            $assetQuery = "INSERT INTO liquid_assets (declaration_id, type, description, amount) VALUES (?, ?, ?, ?)";
            $assetStmt = $pdo->prepare($assetQuery);
            foreach ($data['liquid_assets'] as $asset) {
                $assetStmt->execute([
                    $declarationId,
                    $asset['type'] ?? '',
                    $asset['description'] ?? '',
                    $asset['amount'] ?? ''
                ]);
            }
        }

        // Insert deposits
        if (!empty($data['deposits'])) {
            $depositQuery = "INSERT INTO deposits (declaration_id, bank_name, amount) VALUES (?, ?, ?)";
            $depositStmt = $pdo->prepare($depositQuery);
            foreach ($data['deposits'] as $deposit) {
                $depositStmt->execute([
                    $declarationId,
                    $deposit['bank_name'] ?? '',
                    $deposit['amount'] ?? 0
                ]);
            }
        }

        // Insert insurance
        if (!empty($data['insurance'])) {
            $insuranceQuery = "INSERT INTO insurance (declaration_id, insurance_name, contract_num, earnings) VALUES (?, ?, ?, ?)";
            $insuranceStmt = $pdo->prepare($insuranceQuery);
            foreach ($data['insurance'] as $insurance) {
                $insuranceStmt->execute([
                    $declarationId,
                    $insurance['insurance_name'] ?? '',
                    $insurance['contract_num'] ?? '',
                    $insurance['earnings'] ?? null
                ]);
            }
        }

        // Insert debts
        if (!empty($data['debts'])) {
            $debtQuery = "INSERT INTO debts (declaration_id, creditor_name, type, amount) VALUES (?, ?, ?, ?)";
            $debtStmt = $pdo->prepare($debtQuery);
            foreach ($data['debts'] as $debt) {
                $debtStmt->execute([
                    $declarationId,
                    $debt['creditor_name'] ?? '',
                    $debt['type'] ?? '',
                    $debt['amount'] ?? 0
                ]);
            }
        }

        // Insert business participations
        if (!empty($data['business'])) {
            $businessQuery = "INSERT INTO bussiness (declaration_id, business_name, business_type, participation_type) VALUES (?, ?, ?, ?)";
            $businessStmt = $pdo->prepare($businessQuery);
            foreach ($data['business'] as $business) {
                $businessStmt->execute([
                    $declarationId,
                    $business['business_name'] ?? '',
                    $business['business_type'] ?? '',
                    $business['participation_type'] ?? ''
                ]);
            }
        }

        // Insert differences
        if (!empty($data['differences'])) {
            $differencesQuery = "INSERT INTO differences (declaration_id, content) VALUES (?, ?)";
            $differencesStmt = $pdo->prepare($differencesQuery);
            $differencesStmt->execute([
                $declarationId,
                $data['differences']['content'] ?? ''
            ]);
        }

        // Insert previous incomes
        if (!empty($data['previous_incomes'])) {
            $previousIncomesQuery = "INSERT INTO previous_incomes (declaration_id, html_content) VALUES (?, ?)";
            $previousIncomesStmt = $pdo->prepare($previousIncomesQuery);
            $previousIncomesStmt->execute([
                $declarationId,
                $data['previous_incomes']['html_content'] ?? ''
            ]);
        }

        // Commit transaction
        $pdo->commit();

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Declaration created successfully',
            'declaration_id' => $declarationId
        ]);
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error creating declaration',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle PUT request for full update
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Get JSON data from request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            throw new Exception('Invalid JSON data');
        }

        // Check if ID is provided
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) {
            throw new Exception('Declaration ID is required');
        }

        // Start transaction
        $pdo->beginTransaction();

        // Update declarations table
        $declarationQuery = "UPDATE declarations SET title = ? WHERE id = ?";
        $declarationStmt = $pdo->prepare($declarationQuery);
        $declarationStmt->execute([$data['title'], $id]);

        // Update personal data
        $personalDataQuery = "UPDATE personal_data SET 
            full_name = ?, 
            office = ?, 
            address = ?, 
            dob = ?, 
            id_number = ?, 
            marital_status = ?, 
            dependants = ?, 
            party_id = ? 
            WHERE declaration_id = ?";
        $personalDataStmt = $pdo->prepare($personalDataQuery);
        $personalDataStmt->execute([
            $data['personal_data']['full_name'],
            $data['personal_data']['office'],
            $data['personal_data']['address'],
            $data['personal_data']['dob'],
            $data['personal_data']['id_number'],
            $data['personal_data']['marital_status'],
            $data['personal_data']['dependants'],
            $data['personal_data']['party_id'],
            $id
        ]);

        // Delete existing related records
        $tables = [
            'properties',
            'vehicles',
            'liquid_assets',
            'deposits',
            'insurance',
            'debts',
            'bussiness',
            'differences',
            'previous_incomes'
        ];

        foreach ($tables as $table) {
            $deleteQuery = "DELETE FROM $table WHERE declaration_id = ?";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->execute([$id]);
        }

        // Insert new properties
        if (!empty($data['properties'])) {
            $propertyQuery = "INSERT INTO properties (declaration_id, location, type, area, topographic_data, rights_burdens, acquisition_mode, acquisition_date, acquisition_value, current_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $propertyStmt = $pdo->prepare($propertyQuery);
            foreach ($data['properties'] as $property) {
                $propertyStmt->execute([
                    $id,
                    $property['location'] ?? '',
                    $property['type'] ?? '',
                    $property['area'] ?? 0,
                    $property['topographic_data'] ?? '',
                    $property['rights_burdens'] ?? '',
                    $property['acquisition_mode'] ?? '',
                    $property['acquisition_date'] ?? null,
                    $property['acquisition_value'] ?? '',
                    $property['current_value'] ?? ''
                ]);
            }
        }

        // Insert new vehicles
        if (!empty($data['vehicles'])) {
            $vehicleQuery = "INSERT INTO vehicles (declaration_id, brand, manu_year, value, type) VALUES (?, ?, ?, ?, ?)";
            $vehicleStmt = $pdo->prepare($vehicleQuery);
            foreach ($data['vehicles'] as $vehicle) {
                $vehicleStmt->execute([
                    $id,
                    $vehicle['brand'] ?? '',
                    $vehicle['manu_year'] ?? null,
                    $vehicle['value'] ?? 0,
                    $vehicle['type'] ?? ''
                ]);
            }
        }

        // Insert new liquid assets
        if (!empty($data['liquid_assets'])) {
            $assetQuery = "INSERT INTO liquid_assets (declaration_id, type, description, amount) VALUES (?, ?, ?, ?)";
            $assetStmt = $pdo->prepare($assetQuery);
            foreach ($data['liquid_assets'] as $asset) {
                $assetStmt->execute([
                    $id,
                    $asset['type'] ?? '',
                    $asset['description'] ?? '',
                    $asset['amount'] ?? ''
                ]);
            }
        }

        // Insert new deposits
        if (!empty($data['deposits'])) {
            $depositQuery = "INSERT INTO deposits (declaration_id, bank_name, amount) VALUES (?, ?, ?)";
            $depositStmt = $pdo->prepare($depositQuery);
            foreach ($data['deposits'] as $deposit) {
                $depositStmt->execute([
                    $id,
                    $deposit['bank_name'] ?? '',
                    $deposit['amount'] ?? 0
                ]);
            }
        }

        // Insert new insurance
        if (!empty($data['insurance'])) {
            $insuranceQuery = "INSERT INTO insurance (declaration_id, insurance_name, contract_num, earnings) VALUES (?, ?, ?, ?)";
            $insuranceStmt = $pdo->prepare($insuranceQuery);
            foreach ($data['insurance'] as $insurance) {
                $insuranceStmt->execute([
                    $id,
                    $insurance['insurance_name'] ?? '',
                    $insurance['contract_num'] ?? '',
                    $insurance['earnings'] ?? null
                ]);
            }
        }

        // Insert new debts
        if (!empty($data['debts'])) {
            $debtQuery = "INSERT INTO debts (declaration_id, creditor_name, type, amount) VALUES (?, ?, ?, ?)";
            $debtStmt = $pdo->prepare($debtQuery);
            foreach ($data['debts'] as $debt) {
                $debtStmt->execute([
                    $id,
                    $debt['creditor_name'] ?? '',
                    $debt['type'] ?? '',
                    $debt['amount'] ?? 0
                ]);
            }
        }

        // Insert new business participations
        if (!empty($data['business'])) {
            $businessQuery = "INSERT INTO bussiness (declaration_id, business_name, business_type, participation_type) VALUES (?, ?, ?, ?)";
            $businessStmt = $pdo->prepare($businessQuery);
            foreach ($data['business'] as $business) {
                $businessStmt->execute([
                    $id,
                    $business['business_name'] ?? '',
                    $business['business_type'] ?? '',
                    $business['participation_type'] ?? ''
                ]);
            }
        }

        // Insert new differences
        if (!empty($data['differences'])) {
            $differencesQuery = "INSERT INTO differences (declaration_id, content) VALUES (?, ?)";
            $differencesStmt = $pdo->prepare($differencesQuery);
            $differencesStmt->execute([
                $id,
                $data['differences']['content'] ?? ''
            ]);
        }

        // Insert new previous incomes
        if (!empty($data['previous_incomes'])) {
            $previousIncomesQuery = "INSERT INTO previous_incomes (declaration_id, html_content) VALUES (?, ?)";
            $previousIncomesStmt = $pdo->prepare($previousIncomesQuery);
            $previousIncomesStmt->execute([
                $id,
                $data['previous_incomes']['html_content'] ?? ''
            ]);
        }

        // Commit transaction
        $pdo->commit();

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Declaration updated successfully',
            'declaration_id' => $id
        ]);
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error updating declaration',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle PATCH request for partial update
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    try {
        // Get JSON data from request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            throw new Exception('Invalid JSON data');
        }

        // Check if ID is provided
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) {
            throw new Exception('Declaration ID is required');
        }

        // Start transaction
        $pdo->beginTransaction();

        // Update declarations table if title is provided
        if (isset($data['title'])) {
            $declarationQuery = "UPDATE declarations SET title = ? WHERE id = ?";
            $declarationStmt = $pdo->prepare($declarationQuery);
            $declarationStmt->execute([$data['title'], $id]);
        }

        // Update personal data if provided
        if (isset($data['personal_data']) && is_array($data['personal_data'])) {
            $updateFields = [];
            $params = [];
            
            $allowedFields = [
                'full_name',
                'office',
                'address',
                'dob',
                'id_number',
                'marital_status',
                'dependants',
                'party_id'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($data['personal_data'][$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data['personal_data'][$field];
                }
            }
            
            if (!empty($updateFields)) {
                $params[] = $id;
                $personalDataQuery = "UPDATE personal_data SET " . implode(', ', $updateFields) . " WHERE declaration_id = ?";
                $personalDataStmt = $pdo->prepare($personalDataQuery);
                $personalDataStmt->execute($params);
            }
        }

        // Update other tables if provided
        $tables = [
            'properties' => ['location', 'type', 'area', 'topographic_data', 'rights_burdens', 'acquisition_mode', 'acquisition_date', 'acquisition_value', 'current_value'],
            'vehicles' => ['brand', 'manu_year', 'value', 'type'],
            'liquid_assets' => ['type', 'description', 'amount'],
            'deposits' => ['bank_name', 'amount'],
            'insurance' => ['insurance_name', 'contract_num', 'earnings'],
            'debts' => ['creditor_name', 'type', 'amount'],
            'bussiness' => ['business_name', 'business_type', 'participation_type'],
            'differences' => ['content'],
            'previous_incomes' => ['html_content']
        ];

        foreach ($tables as $table => $fields) {
            if (isset($data[$table])) {
                // Delete existing records
                $deleteQuery = "DELETE FROM $table WHERE declaration_id = ?";
                $deleteStmt = $pdo->prepare($deleteQuery);
                $deleteStmt->execute([$id]);

                // Insert new records
                if (!empty($data[$table])) {
                    $insertFields = array_merge(['declaration_id'], $fields);
                    $placeholders = array_fill(0, count($insertFields), '?');
                    $insertQuery = "INSERT INTO $table (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $insertStmt = $pdo->prepare($insertQuery);

                    foreach ($data[$table] as $record) {
                        $params = [$id];
                        foreach ($fields as $field) {
                            $params[] = $record[$field] ?? null;
                        }
                        $insertStmt->execute($params);
                    }
                }
            }
        }

        // Commit transaction
        $pdo->commit();

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Declaration partially updated successfully',
            'declaration_id' => $id
        ]);
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error updating declaration',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Declaration ID is required']);
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete related records first
        $tables = [
            'personal_data',
            'properties',
            'vehicles',
            'liquid_assets',
            'deposits',
            'insurance',
            'debts',
            'bussiness',
            'differences',
            'previous_incomes',
            'favorites'
        ];

        foreach ($tables as $table) {
            $deleteQuery = "DELETE FROM $table WHERE declaration_id = ?";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->execute([$id]);
        }

        // Delete the declaration
        $deleteDeclarationQuery = "DELETE FROM declarations WHERE id = ?";
        $deleteDeclarationStmt = $pdo->prepare($deleteDeclarationQuery);
        $deleteDeclarationStmt->execute([$id]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Declaration deleted successfully'
        ]);
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error deleting declaration',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Check if specific ID is requested
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id) {
    // Query for specific declaration
    $query = "SELECT 
        d.id,
        d.title,
        d.status,
        d.submission_date,
        pd.full_name,
        pd.office,
        pd.address,
        pd.dob,
        pd.id_number,
        pd.marital_status,
        pd.dependants,
        p.name as party_name,
        sp.year as submission_year,
        d.image_url,
        (SELECT COUNT(*) FROM favorites f WHERE f.declaration_id = d.id) as favorite_count
        FROM declarations d 
        LEFT JOIN personal_data pd ON d.id = pd.declaration_id 
        LEFT JOIN parties p ON pd.party_id = p.id 
        LEFT JOIN submission_periods sp ON d.submission_period_id = sp.id 
        WHERE d.id = ?";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $declaration = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($declaration) {
            // Get properties data
            $propertiesQuery = "SELECT * FROM properties WHERE declaration_id = ?";
            $propertiesStmt = $pdo->prepare($propertiesQuery);
            $propertiesStmt->execute([$id]);
            $declaration['properties'] = $propertiesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get vehicles data
            $vehiclesQuery = "SELECT * FROM vehicles WHERE declaration_id = ?";
            $vehiclesStmt = $pdo->prepare($vehiclesQuery);
            $vehiclesStmt->execute([$id]);
            $declaration['vehicles'] = $vehiclesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get liquid assets data
            $assetsQuery = "SELECT * FROM liquid_assets WHERE declaration_id = ?";
            $assetsStmt = $pdo->prepare($assetsQuery);
            $assetsStmt->execute([$id]);
            $declaration['liquid_assets'] = $assetsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get deposits data
            $depositsQuery = "SELECT * FROM deposits WHERE declaration_id = ?";
            $depositsStmt = $pdo->prepare($depositsQuery);
            $depositsStmt->execute([$id]);
            $declaration['deposits'] = $depositsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get insurance data
            $insuranceQuery = "SELECT * FROM insurance WHERE declaration_id = ?";
            $insuranceStmt = $pdo->prepare($insuranceQuery);
            $insuranceStmt->execute([$id]);
            $declaration['insurance'] = $insuranceStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get debts data
            $debtsQuery = "SELECT * FROM debts WHERE declaration_id = ?";
            $debtsStmt = $pdo->prepare($debtsQuery);
            $debtsStmt->execute([$id]);
            $declaration['debts'] = $debtsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get business participations data
            $businessQuery = "SELECT * FROM bussiness WHERE declaration_id = ?";
            $businessStmt = $pdo->prepare($businessQuery);
            $businessStmt->execute([$id]);
            $declaration['business'] = $businessStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get differences data
            $differencesQuery = "SELECT * FROM differences WHERE declaration_id = ?";
            $differencesStmt = $pdo->prepare($differencesQuery);
            $differencesStmt->execute([$id]);
            $declaration['differences'] = $differencesStmt->fetch(PDO::FETCH_ASSOC);

            // Get previous incomes data
            $previousIncomesQuery = "SELECT * FROM previous_incomes WHERE declaration_id = ?";
            $previousIncomesStmt = $pdo->prepare($previousIncomesQuery);
            $previousIncomesStmt->execute([$id]);
            $declaration['previous_incomes'] = $previousIncomesStmt->fetch(PDO::FETCH_ASSOC);

            $response = [
                'status' => 'success',
                'data' => $declaration
            ];
        } else {
            http_response_code(404);
            $response = [
                'status' => 'error',
                'message' => 'Declaration not found'
            ];
        }
    } catch(PDOException $e) {
        http_response_code(500);
        $response = [
            'status' => 'error',
            'message' => 'Database error occurred',
            'error' => $e->getMessage()
        ];
    }
} else {
    // Original search query code
    $search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
    $year = isset($_GET['year']) ? htmlspecialchars($_GET['year']) : '';
    $position = isset($_GET['position']) ? htmlspecialchars($_GET['position']) : '';
    $party = isset($_GET['party']) ? htmlspecialchars($_GET['party']) : '';
    $status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : 'Approved';

    // Base query
    $query = "SELECT 
        d.id,
        d.title,
        d.status,
        d.submission_date,
        pd.full_name,
        pd.office,
        pd.address,
        pd.dob,
        pd.id_number,
        pd.marital_status,
        pd.dependants,
        p.name as party_name,
        sp.year as submission_year,
        d.image_url,
        (SELECT COUNT(*) FROM favorites f WHERE f.declaration_id = d.id) as favorite_count
        FROM declarations d 
        LEFT JOIN personal_data pd ON d.id = pd.declaration_id 
        LEFT JOIN parties p ON pd.party_id = p.id 
        LEFT JOIN submission_periods sp ON d.submission_period_id = sp.id 
        WHERE 1=1";

    $params = array();

    // Add search conditions
    if (!empty($search)) {
        $query .= " AND (pd.full_name LIKE ? OR pd.office LIKE ? OR d.title LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($year)) {
        $query .= " AND sp.year = ?";
        $params[] = $year;
    }

    if (!empty($position)) {
        $query .= " AND pd.office = ?";
        $params[] = $position;
    }

    if (!empty($party)) {
        $query .= " AND p.name = ?";
        $params[] = $party;
    }

    if (!empty($status)) {
        $query .= " AND d.status = ?";
        $params[] = $status;
    }

    // Add sorting
    $query .= " ORDER BY d.submission_date DESC";

    try {
        // Prepare and execute query
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For each declaration, fetch all related data
        foreach ($declarations as &$declaration) {
            // Get properties data
            $propertiesQuery = "SELECT * FROM properties WHERE declaration_id = ?";
            $propertiesStmt = $pdo->prepare($propertiesQuery);
            $propertiesStmt->execute([$declaration['id']]);
            $declaration['properties'] = $propertiesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get vehicles data
            $vehiclesQuery = "SELECT * FROM vehicles WHERE declaration_id = ?";
            $vehiclesStmt = $pdo->prepare($vehiclesQuery);
            $vehiclesStmt->execute([$declaration['id']]);
            $declaration['vehicles'] = $vehiclesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get liquid assets data
            $assetsQuery = "SELECT * FROM liquid_assets WHERE declaration_id = ?";
            $assetsStmt = $pdo->prepare($assetsQuery);
            $assetsStmt->execute([$declaration['id']]);
            $declaration['liquid_assets'] = $assetsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get deposits data
            $depositsQuery = "SELECT * FROM deposits WHERE declaration_id = ?";
            $depositsStmt = $pdo->prepare($depositsQuery);
            $depositsStmt->execute([$declaration['id']]);
            $declaration['deposits'] = $depositsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get insurance data
            $insuranceQuery = "SELECT * FROM insurance WHERE declaration_id = ?";
            $insuranceStmt = $pdo->prepare($insuranceQuery);
            $insuranceStmt->execute([$declaration['id']]);
            $declaration['insurance'] = $insuranceStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get debts data
            $debtsQuery = "SELECT * FROM debts WHERE declaration_id = ?";
            $debtsStmt = $pdo->prepare($debtsQuery);
            $debtsStmt->execute([$declaration['id']]);
            $declaration['debts'] = $debtsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get business participations data
            $businessQuery = "SELECT * FROM bussiness WHERE declaration_id = ?";
            $businessStmt = $pdo->prepare($businessQuery);
            $businessStmt->execute([$declaration['id']]);
            $declaration['business'] = $businessStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get differences data
            $differencesQuery = "SELECT * FROM differences WHERE declaration_id = ?";
            $differencesStmt = $pdo->prepare($differencesQuery);
            $differencesStmt->execute([$declaration['id']]);
            $declaration['differences'] = $differencesStmt->fetch(PDO::FETCH_ASSOC);

            // Get previous incomes data
            $previousIncomesQuery = "SELECT * FROM previous_incomes WHERE declaration_id = ?";
            $previousIncomesStmt = $pdo->prepare($previousIncomesQuery);
            $previousIncomesStmt->execute([$declaration['id']]);
            $declaration['previous_incomes'] = $previousIncomesStmt->fetch(PDO::FETCH_ASSOC);
        }

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM ($query) as count_query";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $response = [
            'status' => 'success',
            'total_count' => $totalCount,
            'data' => $declarations
        ];
    } catch(PDOException $e) {
        http_response_code(500);
        $response = [
            'status' => 'error',
            'message' => 'Database error occurred',
            'error' => $e->getMessage()
        ];
    }
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?> 
