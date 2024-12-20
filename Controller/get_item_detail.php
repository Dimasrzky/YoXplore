// Controller/get_item_detail.php
<?php
require_once('../Config/db_connect.php');

$item_id = $_GET['id'];

try {
    // Query untuk mendapatkan detail item
    $stmt = $conn->prepare("
        SELECT i.*, c.name as category_name,
        GROUP_CONCAT(DISTINCT img.image_url) as images,
        AVG(r.rating) as avg_rating,
        COUNT(r.id) as total_reviews
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN item_images img ON i.id = img.item_id
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = ?
        GROUP BY i.id
    ");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query untuk mendapatkan review
    $stmt = $conn->prepare("
        SELECT r.*, c.username as reviewer_name, c.profile_image as reviewer_image,
        GROUP_CONCAT(ri.image_url) as review_images
        FROM reviews r
        JOIN client c ON r.user_id = c.id
        LEFT JOIN review_images ri ON r.id = ri.review_id
        WHERE r.item_id = ?
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$item_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'item' => $item,
        'reviews' => $reviews
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>