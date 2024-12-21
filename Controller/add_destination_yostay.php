<?php
// Pastikan tidak ada whitespace sebelum <?php
require_once('../Config/db_connect.php');

// Tambahkan header JSON
header('Content-Type: application/json');

// Tambahkan error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input
        if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['address'])) {
            throw new Exception('Semua field harus diisi');
        }

        $name = $_POST['name'];
        $address = $_POST['address'];
        $openTime = $_POST['openTime'];
        $feature_type = 'YoStay';
        $category_id = $_POST['category'];
        
        // Validasi file upload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error pada upload gambar');
        }

        // Handle image upload
        $image = $_FILES['image'];
        $imageData = file_get_contents($image['tmp_name']);

        $stmt = $conn->prepare("
            INSERT INTO items (name, category_id, feature_type, address, opening_hours)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category_id, $feature_type, $address, $openTime]);
        
        $item_id = $conn->lastInsertId();
        
        // Save image
        $stmt = $conn->prepare("
            INSERT INTO item_images (item_id, image_url, is_main)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$item_id, $imageData]);
        
        echo json_encode(['success' => true]);
        
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