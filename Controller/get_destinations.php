<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    // Get main item details
    $stmt = $conn->prepare("
        SELECT i.*, 
               c.name as category_name,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.id) as review_count
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = ?
        GROUP BY i.id
    ");
    
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        throw new Exception('Item tidak ditemukan');
    }

    // Get all images for this item
    $stmt = $conn->prepare("
        SELECT image_url, is_main
        FROM item_images
        WHERE item_id = ?
        ORDER BY is_main DESC, id ASC
    ");
    $stmt->execute([$_GET['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get reviews with user info
    $stmt = $conn->prepare("
        SELECT 
            r.*,
            u.username,
            u.profile_image,
            GROUP_CONCAT(ri.image_url) as review_images
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN review_images ri ON r.id = ri.review_id
        WHERE r.item_id = ?
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$_GET['id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert main item image to base64 if exists
    if (!empty($item['main_image'])) {
        $item['main_image'] = base64_encode($item['main_image']);
    }

    // Convert additional images to base64
    foreach ($images as &$img) {
        if (!empty($img['image_url'])) {
            $img['image_url'] = base64_encode($img['image_url']);
        }
    }

    // Convert review images and profile images to base64
    foreach ($reviews as &$review) {
        if (!empty($review['profile_image'])) {
            $review['profile_image'] = base64_encode($review['profile_image']);
        }
        if (!empty($review['review_images'])) {
            $reviewImagesArray = explode(',', $review['review_images']);
            $review['images'] = array_map('base64_encode', $reviewImagesArray);
        }
    }

    // Get similar items (same category)
    $stmt = $conn->prepare("
        SELECT i.id, i.name, i.address,
               COALESCE(im.image_url, '') as main_image
        FROM items i
        LEFT JOIN item_images im ON i.id = im.item_id AND im.is_main = 1
        WHERE i.category_id = ? 
        AND i.id != ?
        LIMIT 4
    ");
    $stmt->execute([$item['category_id'], $item['id']]);
    $similarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert similar items images to base64
    foreach ($similarItems as &$similarItem) {
        if (!empty($similarItem['main_image'])) {
            $similarItem['main_image'] = base64_encode($similarItem['main_image']);
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'item' => $item,
            'images' => $images,
            'reviews' => $reviews,
            'similar_items' => $similarItems
        ]
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>