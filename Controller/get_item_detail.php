<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    $itemId = $_GET['id'];
    
    // Debug output
    error_log("Processing request for ID: $itemId");

    $stmt = $conn->prepare("
        SELECT i.* 
        FROM items i 
        WHERE i.id = ?
    ");
    
    if (!$stmt->execute([$itemId])) {
        throw new Exception("Query execution failed");
    }
    
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug output
    error_log("Item data: " . print_r($item, true));

    if (!$item) {
        throw new Exception("Item dengan ID $itemId tidak ditemukan");
    }

    // Get images
    $imgStmt = $conn->prepare("
        SELECT image_url, is_main 
        FROM item_images 
        WHERE item_id = ?
    ");
    
    if (!$imgStmt->execute([$itemId])) {
        throw new Exception("Image query execution failed");
    }
    
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure we're only outputting once and with proper JSON format
    $response = [
        'success' => true,
        'item' => $item,
        'images' => $images
    ];

    // Debug output
    error_log("Sending response: " . json_encode($response));

    echo json_encode($response);
    exit;

} catch(Exception $e) {
    error_log("Error in get_item_detail: " . $e->getMessage());
    
    $errorResponse = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    echo json_encode($errorResponse);
    exit;
}
?>