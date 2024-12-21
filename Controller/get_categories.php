<?php
require_once '../Config/db_connect.php';
header('Content-Type: application/json');

try {
    $feature_type = $_GET['type'] ?? '';
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE feature_type = ?");
    $stmt->execute([$feature_type]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $categories
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}