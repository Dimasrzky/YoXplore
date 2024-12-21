<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input
        if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['address'])) {
            throw new Exception('Mohon isi semua field yang diperlukan');
        }

        // Cek ukuran file
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
            throw new Exception('Ukuran file terlalu besar (max 5MB)');
        }

        $conn->beginTransaction();

        // Insert item dulu
        $stmt = $conn->prepare("
            INSERT INTO items (name, category_id, feature_type, address, opening_hours)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            'YoStay',
            $_POST['address'],
            $_POST['openTime']
        ]);
        
        $item_id = $conn->lastInsertId();

        // Handle image - simpan sebagai base64 atau path file
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image'];
            $imageData = base64_encode(file_get_contents($image['tmp_name']));
            
            $stmt = $conn->prepare("
                INSERT INTO item_images (item_id, image_url, is_main)
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$item_id, $imageData]);
        }

        $conn->commit();
        echo json_encode(['success' => true]);
        
    } catch(Exception $e) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}