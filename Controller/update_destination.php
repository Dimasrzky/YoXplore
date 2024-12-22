<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['category'])) {
        throw new Exception('Semua field harus diisi');
    }

    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare("
            UPDATE items 
            SET name = ?, 
                category_id = ?, 
                address = ?, 
                opening_hours = ?,
                closing_hours = ?,
                phone = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['address'],
            $_POST['openTime'],
            $_POST['closeTime'],
            $_POST['phone'] ?? null,
            $_POST['id']
        ]);

        // Update image jika ada
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            
            // Hapus image lama
            $stmt = $conn->prepare("DELETE FROM item_images WHERE item_id = ? AND is_main = 1");
            $stmt->execute([$_POST['id']]);
            
            // Insert image baru
            $stmt = $conn->prepare("
                INSERT INTO item_images (item_id, image_url, is_main)
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$_POST['id'], $imageData]);
        }

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil diupdate'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}