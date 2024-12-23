// Controller/get_item_detail.php
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
               COUNT(r.id) as review_count
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

    // Get all images
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
        SELECT r.*, u.username, u.profile_image,
               GROUP_CONCAT(ri.image_url) as review_images
        FROM reviews r
        LEFT JOIN client u ON r.user_id = u.id
        LEFT JOIN review_images ri ON r.id = ri.review_id
        WHERE r.item_id = ?
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$_GET['id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process the images to base64
    foreach ($images as &$img) {
        if (!empty($img['image_url'])) {
            $img['image_url'] = base64_encode($img['image_url']);
        }
    }

    // Process review images
    foreach ($reviews as &$review) {
        if (!empty($review['review_images'])) {
            $review['images'] = array_map('base64_encode', 
                explode(',', $review['review_images']));
        }
        if (!empty($review['profile_image'])) {
            $review['profile_image'] = base64_encode($review['profile_image']);
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'item' => $item,
            'images' => $images,
            'reviews' => $reviews
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

