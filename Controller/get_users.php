<?php
require_once '../Config/db_connect.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id, username, email, created_at FROM client ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pastikan output JSON bersih
    $response = [
        'success' => true,
        'data' => array_map(function($user) {
            return [
                'id' => $user['id'],
                'username' => htmlspecialchars($user['username']),
                'email' => htmlspecialchars($user['email']),
                'created_at' => $user['created_at']
            ];
        }, $users),
        'count' => count($users)
    ];

    echo json_encode($response);
    exit;

} catch(PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ];
    echo json_encode($response);
    exit;
}