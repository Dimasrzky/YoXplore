<?php
require_once __DIR__ . '/../Config/db_connect.php';

try {
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM client");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($users);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>