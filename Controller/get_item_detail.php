<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $item_id = $_GET['id'] ?? null;
    
    if (!$item_id) {
        throw new Exception('ID item tidak ditemukan');
    }

    // Query untuk mengambil detail item
    $query = "
        SELECT i.*, 
               c.name as category_name,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(r.id) as review_count
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = :item_id
        GROUP BY i.id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk mengambil gambar item
    $query_images = "
        SELECT image_url, is_main 
        FROM item_images 
        WHERE item_id = :item_id
    ";
    
    $stmt = $conn->prepare($query_images);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk mengambil review
    $query_reviews = "
        SELECT r.*, u.username, u.profile_image 
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.item_id = :item_id
        ORDER BY r.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $conn->prepare($query_reviews);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Konversi gambar ke base64
    foreach ($images as &$image) {
        if (!empty($image['image_url'])) {
            $image['image_url'] = base64_encode($image['image_url']);
        }
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
?>