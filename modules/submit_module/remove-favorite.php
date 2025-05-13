<?php
session_start();
require_once '../../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_POST['declaration_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing data']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND declaration_id = ?");
    $stmt->execute([$_SESSION['id'], $_POST['declaration_id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 