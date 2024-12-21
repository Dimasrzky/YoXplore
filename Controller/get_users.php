<?php
require_once __DIR__ . '/../Config/db_connect.php';

// Matikan semua output buffer
ob_clean();
header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT id, username, email, created_at FROM client ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pastikan tidak ada output lain sebelum JSON
    echo json_encode([
        'success' => true,
        'data' => $users,
        'count' => count($users)
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;