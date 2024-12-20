<?php
require_once('../Config/db_connect.php');

header('Content-Type: application/json');

try {
    $query = "SELECT * FROM client ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
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
        'message' => 'Failed to fetch users: ' . $e->getMessage()
    ]);
}
?>