<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    // Debug connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    $itemId = $_GET['id'];
    
    // Debug: Log the query
    error_log("Querying for item ID: " . $itemId);

    $stmt = $conn->prepare("
        SELECT i.* 
        FROM items i 
        WHERE i.id = ?
    ");
    
    $stmt->execute([$itemId]);
    
    // Debug: Log the query result
    error_log("Query executed");
    
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: Print the result
    error_log("Query result: " . print_r($item, true));

    if (!$item) {
        throw new Exception("Item dengan ID $itemId tidak ditemukan");
    }

    // Get images
    $imgStmt = $conn->prepare("
        SELECT image_url, is_main 
        FROM item_images 
        WHERE item_id = ?
    ");
    $imgStmt->execute([$itemId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'item' => $item,
        'images' => $images,
        'debug' => [
            'id' => $itemId,
            'hasItem' => !empty($item),
            'imageCount' => count($images)
        ]
    ]);

} catch(Exception $e) {
    error_log("Error in get_item_detail: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>