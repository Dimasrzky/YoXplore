<?php
session_start();
require_once('../Config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $profileImage = $_POST['profileImage']; // Base64 image data

    try {
        // Verify password
        $stmt = $conn->prepare("SELECT password FROM client WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($password, $user['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Incorrect password'
            ]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE client SET username = ?, profile_image = ? WHERE id = ?");
        $stmt->execute([$username, $profileImage, $userId]);
        
        $_SESSION['username'] = $username;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>