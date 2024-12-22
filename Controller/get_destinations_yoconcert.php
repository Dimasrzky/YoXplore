<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $section = $_GET['section'] ?? 'YoConcert';
    $category = $_GET['category'] ?? null;
    
    $query = "
        SELECT i.*, 
               COALESCE(im.image_url, '') as main_image,
               c.name as category_name
        FROM items i
        LEFT JOIN (
            SELECT item_id, image_url
            FROM item_images
            WHERE is_main = 1
        ) im ON i.id = im.item_id
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.feature_type = ?
    ";
    
    $params = [$section];
    
    if ($category) {
        $query .= " AND c.name = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY i.id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert image data to base64
    foreach ($data as &$item) {
        if (!empty($item['main_image'])) {
            $item['main_image'] = base64_encode($item['main_image']);
        }
    }
    
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
?>