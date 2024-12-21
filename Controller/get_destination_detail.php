<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    $stmt = $conn->prepare("
        SELECT i.*, im.image_url as main_image
        FROM items i
        LEFT JOIN item_images im ON i.id = im.item_id AND im.is_main = 1
        WHERE i.id = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        if (!empty($data['main_image'])) {
            $data['main_image'] = base64_encode($data['main_image']);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        throw new Exception('Data tidak ditemukan');
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}