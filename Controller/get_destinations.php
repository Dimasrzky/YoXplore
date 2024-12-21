<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    // Join dengan table item_images untuk mendapatkan gambar
    $stmt = $conn->prepare("
        SELECT i.*, im.image_url as main_image 
        FROM items i 
        LEFT JOIN item_images im ON i.id = im.item_id AND im.is_main = 1
        WHERE i.feature_type = ?
        ORDER BY i.id DESC
    ");
    
    $stmt->execute(['YoStay']);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}