<?php
require_once('../Config/db_connect.php');

$data = json_decode(file_get_contents('php://input'), true);

try {
    if(isset($data['password']) && !empty($data['password'])) {
        $sql = "UPDATE client SET username = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->execute([$data['username'], $data['email'], $hashedPassword, $data['id']]);
    } else {
        $sql = "UPDATE client SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$data['username'], $data['email'], $data['id']]);
    }
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>