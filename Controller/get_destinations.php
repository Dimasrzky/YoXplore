<?php
require_once '../Config/db_connect.php';

header('Content-Type: application/json');

try {
    $section = $_GET['section'] ?? '';
    
    $sql = "SELECT i.*, c.name as category_name 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.id
            WHERE i.feature_type = ?
            ORDER BY i.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$section]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $items,
        'count' => count($items)
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}