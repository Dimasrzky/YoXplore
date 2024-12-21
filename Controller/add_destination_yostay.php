<?php
require_once('../Config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $openTime = $_POST['openTime'];
    $feature_type = 'YoStay';
    $category_id = $_POST['category'];
    
    // Handle image upload
    $image = $_FILES['image'];
    $imageData = file_get_contents($image['tmp_name']);

    try {
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
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}