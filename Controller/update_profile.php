<?php
session_start();
require_once('../Config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $username = $_POST['username'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $profileImage = $_POST['profileImage'] ?? null;

    try {
        // Verifikasi password saat ini
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
        $sql = "UPDATE client SET username = ?";
        $params = [$username];

        if (!empty($newPassword)) {
            $sql .= ", password = ?";
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if ($profileImage) {
            $sql .= ", profile_image = ?";
            $params[] = $profileImage;
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $_SESSION['username'] = $username;
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>