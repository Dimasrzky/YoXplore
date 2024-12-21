<?php
require_once '../Config/db_connect.php';

header('Content-Type: application/json');

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    $sql = "SELECT id, username, email, created_at FROM client ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users,
        'count' => count($users)
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}