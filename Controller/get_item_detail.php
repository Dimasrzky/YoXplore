<?php
// Pastikan tidak ada whitespace sebelum <?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    $itemId = intval($_GET['id']);

    // Query item details
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode([
            'success' => false,
            'message' => 'Item tidak ditemukan'
        ]);
        exit;
    }

    // Query images
    $imgStmt = $conn->prepare("SELECT image_url, is_main FROM item_images WHERE item_id = ?");
    $imgStmt->execute([$itemId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'success' => true,
        'item' => $item,
        'images' => $images
    ];

    // Send response
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch(Exception $e) {
    error_log("Error in get_item_detail: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}