<?php
header('Content-Type: application/json');

try {
    require_once('../Config/db_connect.php');
    
    // Set timeout lebih lama
    $conn->setAttribute(PDO::ATTR_TIMEOUT, 60);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['address'])) {
            throw new Exception('Semua field harus diisi');
        }

        $conn->beginTransaction();

        try {
            // Insert data destinasi
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
            
            // Handle image upload
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
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan destinasi: ' . $e->getMessage()
    ]);
}