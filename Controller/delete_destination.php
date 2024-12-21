<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    $conn->beginTransaction();

    // Hapus gambar terkait
    $stmt = $conn->prepare("DELETE FROM item_images WHERE item_id = ?");
    $stmt->execute([$data['id']]);
    
    // Hapus item
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Data berhasil dihapus'
    ]);

} catch(Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}