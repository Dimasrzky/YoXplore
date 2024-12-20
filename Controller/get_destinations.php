<?php
require_once '../Config/db_connect.php';
require_once '../Config/session_check.php';

header('Content-Type: application/json');

$feature_type = $_GET['section'] ?? '';

try {
    $sql = "SELECT i.*, c.name as category_name, 
            GROUP_CONCAT(img.id) as image_ids
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.id
            LEFT JOIN item_images img ON i.id = img.item_id
            WHERE i.feature_type = ?
            GROUP BY i.id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$feature_type]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($items as &$item) {
        $imageIds = $item['image_ids'] ? explode(',', $item['image_ids']) : [];
        $item['images'] = array_map(function($imgId) {
            return "../Controller/get_image.php?id=" . $imgId;
        }, $imageIds);
        
        $item['main_image'] = !empty($item['images']) ? $item['images'][0] : '/Image/placeholder.jpg';
    }
    
    echo json_encode([
        'success' => true,
        'data' => $items
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}