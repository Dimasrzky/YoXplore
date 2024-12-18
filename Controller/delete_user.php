<?php
require_once __DIR__ . '/../Config/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

try {
    if (!$id) throw new Exception('Invalid ID');
    
    $stmt = $conn->prepare("DELETE FROM client WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>