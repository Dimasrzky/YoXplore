<?php
header('Content-Type: application/json'); 
require_once('../Config/db_connect.php');

try {
    // Validasi input
    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    $itemId = intval($_GET['id']);
    
    // Query utama
    $stmt = $conn->prepare("
        SELECT * FROM items WHERE id = ?
    ");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item tidak ditemukan");
    }

    // Query gambar
    $imgStmt = $conn->prepare("
        SELECT image_url, is_main 
        FROM item_images 
        WHERE item_id = ?
    ");
    $imgStmt->execute([$itemId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response
    $response = [
        'success' => true,
        'item' => $item,
        'images' => $images
    ];

    // Send response
    echo json_encode($response);
    exit();

} catch(Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
    exit();
}
?>