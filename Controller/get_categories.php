<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $type = $_GET['type'] ?? '';
    
    $stmt = $conn->prepare("
        SELECT * FROM categories 
        WHERE feature_type = ?
        ORDER BY name ASC
    ");
    
    $stmt->execute([$type]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}