<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $itemId = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$itemId) {
        throw new Exception('Item ID is required');
    }

    // Get reviews with user info
    $query = "
        SELECT 
            r.id,
            r.user_id,
            r.rating,
            r.review_text,
            r.created_at,
            c.username,
            c.profile_image
        FROM reviews r
        LEFT JOIN client c ON r.user_id = c.id
        WHERE r.item_id = ?
        ORDER BY r.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$itemId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get images for each review
    foreach ($reviews as &$review) {
        $imgStmt = $conn->prepare("
            SELECT image_url 
            FROM review_images 
            WHERE review_id = ?
        ");
        $imgStmt->execute([$review['id']]);
        $review['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode([
        'success' => true,
        'data' => $reviews
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>