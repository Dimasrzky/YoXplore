<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../Config/db_connect.php');

try {
    $itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$itemId) {
        throw new PDOException('Invalid ID');
    }
    
    $sql = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        throw new PDOException('Item not found');
    }
    
    echo json_encode([
        'item' => $item,
        'rating' => ['average' => '0', 'total' => 0],
        'images' => ['../Image/placeholder.png']
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}