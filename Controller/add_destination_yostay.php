<?php
// Tambahkan ini di awal file untuk debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once('../Config/db_connect.php');

// Log data yang diterima
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input
        if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['address'])) {
            throw new Exception('Mohon isi semua field yang diperlukan');
        }

        $name = $_POST['name'];
        $address = $_POST['address'];
        $openTime = $_POST['openTime'] ?? null;
        $feature_type = 'YoStay';
        $category_id = $_POST['category'];
        
        // Cek apakah ada file yang diupload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Mohon upload gambar');
        }

        $conn->beginTransaction();

        try {
            // Insert item
            $stmt = $conn->prepare("
                INSERT INTO items (name, category_id, feature_type, address, opening_hours)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $category_id, $feature_type, $address, $openTime]);
            
            $item_id = $conn->lastInsertId();
            
            // Handle image
            $image = $_FILES['image'];
            $imageData = file_get_contents($image['tmp_name']);
            
            // Insert image
            $stmt = $conn->prepare("
                INSERT INTO item_images (item_id, image_url, is_main)
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$item_id, $imageData]);

            $conn->commit();
            
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    } catch(Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}