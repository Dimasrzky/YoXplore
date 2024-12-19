<?php
session_start();
require_once('../Config/db_connect.php');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$imageData = $data['image'];

try {
    $stmt = $conn->prepare("UPDATE client SET profile_image = ? WHERE id = ?");
    $stmt->execute([$imageData, $userId]);
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>