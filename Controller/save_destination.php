<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $id = $_POST['destinationId'] ?? null;
    $name = $_POST['name'];
    $address = $_POST['address'];
    $openTime = $_POST['openTime'];
    $feature_type = $_POST['section'];
    
    $pdo->beginTransaction();
    
    if($id) {
        // Update
        $sql = "UPDATE items SET 
                name = ?, 
                address = ?, 
                opening_hours = ?
                WHERE id = ? AND feature_type = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $address, $openTime, $id, $feature_type]);
    } else {
        // Insert
        $sql = "INSERT INTO items (name, address, opening_hours, feature_type) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $address, $openTime, $feature_type]);
        $id = $pdo->lastInsertId();
    }
    
    // Handle image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name);
        $image_url = '/uploads/' . $file_name;
        
        // Save image url to database
        $sql = "INSERT INTO item_images (item_id, image_url, is_main) VALUES (?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $image_url]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}