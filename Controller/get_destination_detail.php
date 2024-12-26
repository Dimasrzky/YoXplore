<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) throw new Exception('Invalid ID');

    // Get main item details
    $stmt = $conn->prepare("
        SELECT i.*, c.name as category_name
        FROM items i 
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.id = ?
    ");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) throw new Exception('Item not found');

    // Get item images
    $imageStmt = $conn->prepare("
        SELECT image_url 
        FROM item_images 
        WHERE item_id = ?
    ");
    $imageStmt->execute([$id]);
    $images = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

    $response = [
        'item' => $item,
        'images' => $images ?: ['../Image/placeholder.png']
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}