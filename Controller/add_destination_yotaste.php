<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['address'])) {
        throw new Exception('Semua field harus diisi');
    }

    $conn->beginTransaction();

    try {
        // Perhatikan jumlah tanda tanya (?) harus sama dengan jumlah parameter di execute
        $stmt = $conn->prepare("
            INSERT INTO items (name, category_id, feature_type, address, opening_hours, closing_hours, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],                 // Parameter 1
            $_POST['category'],             // Parameter 2
            'YoTaste',                      // Parameter 3
            $_POST['address'],              // Parameter 4
            $_POST['openTime'] ?? null,     // Parameter 5
            $_POST['closeTime'] ?? null,    // Parameter 6
            $_POST['phone'] ?? null         // Parameter 7
        ]);
        
        $item_id = $conn->lastInsertId();
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            
            $stmt = $conn->prepare("
                INSERT INTO item_images (item_id, image_url, is_main)
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$item_id, $imageData]);
        }

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil disimpan'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan restaurant: ' . $e->getMessage()
    ]);
}