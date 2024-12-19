<?php
session_start();
require_once('../Config/db_connect.php');

try {
    $userId = $_SESSION['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if(!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE client SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $hashedPassword, $userId]);
    } else {
        $sql = "UPDATE client SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $userId]);
    }
    
    $_SESSION['username'] = $username;
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>