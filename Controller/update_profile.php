<?php
session_start();
require_once('../Config/db_connect.php');

try {
    $userId = $_SESSION['user_id'];
    $username = $_POST['username'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $profileImage = $_POST['profileImage'];
    
    // Cek password lama
    $stmt = $conn->prepare("SELECT password FROM client WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit;
    }
    
    // Update user
    if (!empty($newPassword)) {
        // Jika ada password baru
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE client SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $hashedPassword, $userId]);
    } else {
        // Jika hanya update username
        $sql = "UPDATE client SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $userId]);
    }
    
    $_SESSION['username'] = $username;
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>