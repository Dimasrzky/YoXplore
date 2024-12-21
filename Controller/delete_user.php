<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validasi ID
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('Invalid ID');
    }

    $stmt = $conn->prepare("SELECT id FROM client WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('User not found');
    }

    // Hapus user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$data['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'User berhasil dihapus'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}