<?php
session_start();
require_once '../../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_POST['declaration_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing data']);
    exit();
}

try {
    // Check if already favorited
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND declaration_id = ?");
    $stmt->execute([$_SESSION['id'], $_POST['declaration_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Already favorited']);
        exit();
    }
    
    // Add to favorites
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, declaration_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['id'], $_POST['declaration_id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 