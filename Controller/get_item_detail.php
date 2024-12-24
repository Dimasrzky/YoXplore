<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID not found');
    }

    // Get main item details
    $stmt = $conn->prepare("
        SELECT i.*, 
               i.id as item_id,
               i.name as item_name,
               i.address,
               i.opening_hours,
               i.phone,
               i.rating,
               i.description
        FROM items i
        WHERE i.id = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        throw new Exception('Item not found');
    }

    // Get item images
    $imgStmt = $conn->prepare("
        SELECT image_url, is_main 
        FROM item_images 
        WHERE item_id = ?
        ORDER BY is_main DESC
    ");
    $imgStmt->execute([$_GET['id']]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get reviews
    $reviewStmt = $conn->prepare("
        SELECT r.*, u.username, u.profile_image
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.item_id = ?
        ORDER BY r.created_at DESC
    ");
    $reviewStmt->execute([$_GET['id']]);
    $reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get review images
    $reviewImagesStmt = $conn->prepare("
        SELECT review_id, image_url
        FROM review_images
        WHERE review_id IN (SELECT id FROM reviews WHERE item_id = ?)
    ");
    $reviewImagesStmt->execute([$_GET['id']]);
    $reviewImages = $reviewImagesStmt->fetchAll(PDO::FETCH_GROUP);

    // Add images to reviews
    foreach ($reviews as &$review) {
        $review['images'] = $reviewImages[$review['id']] ?? [];
    }

    echo json_encode([
        'success' => true,
        'item' => $item,
        'images' => $images,
        'reviews' => $reviews
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}