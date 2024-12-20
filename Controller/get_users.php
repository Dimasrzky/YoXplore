<?php
require_once '../Config/db_connect.php'; 

header('Content-Type: application/json');

try {
    $sql = "SELECT id, username, email, created_at FROM client ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug output
    error_log("Found " . count($users) . " users");
    
    echo json_encode([
        'success' => true,
        'data' => $users,
        'count' => count($users)
    ]);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Database error: " . $e->getMessage()
    ]);
}