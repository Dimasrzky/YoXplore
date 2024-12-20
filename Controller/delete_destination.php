<?php
require_once '../Config/db_connect.php';
require_once '../Config/session_check.php';

header('Content-Type: application/json');

try {
    // Get ID from POST request
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        throw new Exception('ID is required');
    }

    $pdo->beginTransaction();

    // First delete related images from item_images
    $sql = "DELETE FROM item_images WHERE item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Then delete related reviews
    $sql = "DELETE FROM reviews WHERE item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Finally delete the main item
    $sql = "DELETE FROM items WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Destination deleted successfully'
    ]);

} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>