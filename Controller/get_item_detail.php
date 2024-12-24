<?php
// Hapus semua whitespace di awal file
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID tidak ditemukan'
        ]);
        exit;
    }

    $itemId = intval($_GET['id']);

    // Debug: Log query parameters
    error_log("Querying item ID: " . $itemId);

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

    $imgStmt = $conn->prepare("SELECT image_url, is_main FROM item_images WHERE item_id = ?");
    $imgStmt->execute([$itemId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'item' => $item,
        'images' => $images
    ];

    // Debug: Log response before sending
    error_log("Sending response: " . json_encode($response));
    
    echo json_encode($response);
    exit;

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
    exit;
} catch(Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'System error'
    ]);
    exit;
}
// Tidak ada kode setelah ini