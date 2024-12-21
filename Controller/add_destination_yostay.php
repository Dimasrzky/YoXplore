<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error ke output

require_once('../Config/db_connect.php');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Validasi input
    if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['address'])) {
        throw new Exception('Semua field harus diisi');
    }

    // Debug: log data yang diterima
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));

    $conn->beginTransaction();

    // Insert ke table items
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
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    error_log('Error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}