<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    // Debug: print received ID
    error_log("Received ID: " . $_GET['id']);

    $stmt = $conn->prepare("
        SELECT i.*, 
               i.id as item_id,
               i.name as item_name,
               i.address,
               i.opening_hours,
               i.phone,
               i.rating,
               i.description
        FROM items i
        WHERE i.id = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: print query result
    error_log("Query result: " . print_r($item, true));

    if (!$item) {
        throw new Exception('Item tidak ditemukan');
    }

    // Get item images
    $imgStmt = $conn->prepare("
        SELECT image_url, is_main 
        FROM item_images 
        WHERE item_id = ?
    ");
    $imgStmt->execute([$_GET['id']]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'item' => $item,
        'images' => $images
    ]);

} catch(Exception $e) {
    error_log("Error in get_item_detail: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}