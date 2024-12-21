<?php
require_once '../Config/db_connect.php';
header('Content-Type: application/json');

// Clear any existing output
ob_clean();

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : 0;

    if (!$id) {
        throw new Exception('Invalid ID');
    }

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM client WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}