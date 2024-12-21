<?php
// Pastikan ini ada di paling atas file
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Log error ke file
ini_set('log_errors', 1);
ini_set('error_log', '../error.log');

require_once('../Config/db_connect.php');

try {
    // Debug: log semua data yang masuk
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Validasi input
    if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['address'])) {
        throw new Exception('Semua field harus diisi');
    }

    // Validasi file upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error pada upload gambar');
    }

    // Validasi koneksi database
    if (!$conn) {
        throw new Exception('Koneksi database tidak tersedia');
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
            $_POST['openTime'] ?? null
        ]);
        
        $item_id = $conn->lastInsertId();
        
        // Handle upload gambar
        $image = $_FILES['image'];
        $imageData = file_get_contents($image['tmp_name']);
        
        $stmt = $conn->prepare("
            INSERT INTO item_images (item_id, image_url, is_main)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$item_id, $imageData]);

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
    error_log("Error in add_destination.php: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan destinasi: ' . $e->getMessage()
    ]);
}