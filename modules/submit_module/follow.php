<?php
session_start();
require_once '../../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_POST['politician_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing data']);
    exit();
}

try {
    // Check if already following
    $stmt = $pdo->prepare("SELECT id FROM follows WHERE user_id = ? AND politician_id = ?");
    $stmt->execute([$_SESSION['id'], $_POST['politician_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Already following']);
        exit();
    }
    
    // Add to follows
    $stmt = $pdo->prepare("INSERT INTO follows (user_id, politician_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['id'], $_POST['politician_id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 