<?php
require_once '../Config/db_connect.php';

header('Content-Type: application/json');

try {
    // Tambahkan error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $sql = "SELECT id, username, email, created_at FROM client ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pastikan output JSON valid
    $response = [
        'success' => true,
        'data' => $users,
        'count' => count($users)
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;

} catch(PDOException $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
    exit;
}