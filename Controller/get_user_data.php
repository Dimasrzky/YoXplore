<?php
session_start();
require_once('../Config/db_connect.php');

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT username, profile_image FROM client WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'username' => $user['username'],
            'profileImage' => $user['profile_image']
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
}
?>