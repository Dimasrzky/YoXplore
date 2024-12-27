<?php
// Controller/get_reviews.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once('../Config/db_connect.php');

try {
    // Debug log untuk melihat parameter yang diterima
    error_log("Received request with parameters: " . print_r($_GET, true));
    
    $itemId = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$itemId) {
        throw new Exception('Item ID is required');
    }

    $query = "
        SELECT 
            r.*,
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

    // Debug log untuk hasil query
    error_log("Query results: " . print_r($reviews, true));

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