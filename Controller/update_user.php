<?php
require_once '../Config/db_connect.php';
header('Content-Type: application/json');


try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'];
    $username = $data['username'];
    $email = $data['email'];
    
    $sql = "UPDATE client SET username = ?, email = ? WHERE id = ?";
    $params = [$username, $email, $id];
    
    if(isset($data['password']) && !empty($data['password'])) {
        $sql = "UPDATE client SET username = ?, email = ?, password = ? WHERE id = ?";
        $params = [$username, $email, password_hash($data['password'], PASSWORD_DEFAULT), $id];
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode([
        'success' => true
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error updating user'
    ]);
}
?>