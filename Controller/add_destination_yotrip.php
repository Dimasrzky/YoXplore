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

    // Cek apakah nama tempat sudah ada untuk YoTrip
    $stmt = $conn->prepare("
        SELECT id FROM items 
        WHERE name = ? AND feature_type = 'YoTrip'
    ");
    $stmt->execute([$_POST['name']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Destination dengan nama tersebut sudah ada');
    }

    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare("
            INSERT INTO items (name, category_id, feature_type, address, opening_hours, closing_hours, phone)
            VALUES (?, ?, 'YoTrip', ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['address'],
            $_POST['openTime'],
            $_POST['closeTime'],
            $_POST['phone'] ?? null
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
        'message' => $e->getMessage()
    ]);
}