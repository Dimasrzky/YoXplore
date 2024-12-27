<?php
// Controller/get_reviews.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once('../Config/db_connect.php');

try {
    // Get item_id from URL parameter
    $itemId = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$itemId) {
        throw new Exception('Item ID is required');
    }

    // Query to get reviews with user information
    $query = "
        SELECT 
            r.id,
            r.user_id,
            r.item_id,
            r.rating,
            r.review_text,
            r.created_at,
            u.username,
            u.profile_image
        FROM reviews r
        LEFT JOIN client u ON r.user_id = u.id
        WHERE r.item_id = ?
        ORDER BY r.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$itemId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug log
    error_log("Found " . count($reviews) . " reviews for item " . $itemId);

    echo json_encode([
        'success' => true,
        'data' => $reviews
    ]);

} catch (Exception $e) {
    error_log("Error in get_reviews.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>