<?php
require_once '../Config/db_connect.php';
require_once '../Config/session_check.php';

header('Content-Type: application/json');

try {
    // Query untuk mengambil data user
    $sql = "SELECT id, username, email, created_at FROM client ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users,
        'count' => count($users)
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}