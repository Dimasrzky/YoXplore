<?php
require_once '../Config/db_connect.php';
require_once '../Config/session_check.php';

header('Content-Type: application/json');

try {
    $id = $_POST['destinationId'] ?? null;
    $name = $_POST['name'];
    $address = $_POST['address'];
    $openTime = $_POST['openTime'];
    $feature_type = $_POST['section'];
    $category_id = $_POST['category_id'] ?? null;
    
    $pdo->beginTransaction();
    
    if($id) {
        $sql = "UPDATE items SET 
                name = ?, 
                category_id = ?,
                address = ?, 
                opening_hours = ?
                WHERE id = ? AND feature_type = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $category_id, $address, $openTime, $id, $feature_type]);
    } else {
        $sql = "INSERT INTO items (name, category_id, address, opening_hours, feature_type) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $category_id, $address, $openTime, $feature_type]);
        $id = $pdo->lastInsertId();
    }
    
    // Handle image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if(!in_array($ext, $allowed)) {
            throw new Exception('Invalid file type');
        }

        // Baca file sebagai binary
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        
        // Update existing main image to not main
        $sql = "UPDATE item_images SET is_main = 0 WHERE item_id = ? AND is_main = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // Save new image as binary
        $sql = "INSERT INTO item_images (item_id, image_url, is_main) VALUES (?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $imageData]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true]);

} catch(Exception $e) {
    if($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}