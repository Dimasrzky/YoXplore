<?php
// get_destination_detail.php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) throw new Exception('Invalid ID');

    $query = "
        SELECT i.*, 
            COALESCE(AVG(r.rating), 0) as rating,
            COUNT(r.id) as total_reviews
        FROM items i
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = :id
        GROUP BY i.id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) throw new Exception('Item not found');

    // Get images
    $imageQuery = "SELECT image_url FROM item_images WHERE item_id = :id";
    $imageStmt = $conn->prepare($imageQuery);
    $imageStmt->execute(['id' => $id]);
    $images = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'item' => $item,
        'images' => $images ?: ['../Image/placeholder.jpg']
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}