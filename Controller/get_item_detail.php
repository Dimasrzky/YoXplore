<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../Config/db_connect.php');

try {
    $itemId = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if (!$itemId) {
        throw new Exception('ID tidak ditemukan');
    }

    // Debug output
    error_log("Processing request for ID: $itemId");

    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare query");
    }

    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item tidak ditemukan");
    }

    // Get images
    $imgStmt = $conn->prepare("SELECT image_url, is_main FROM item_images WHERE item_id = ?");
    $imgStmt->execute([$itemId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'item' => $item,
        'images' => $images
    ];

    // Make sure there's no output before this
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch(Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}