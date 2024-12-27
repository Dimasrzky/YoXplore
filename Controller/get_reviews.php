<?php
// Prevent any unwanted output
ob_start();

// Set header
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Prevent PHP errors from breaking JSON

require_once('../Config/db_connect.php');

try {
    $itemId = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$itemId) {
        throw new Exception('Item ID is required');
    }

    $query = "
        SELECT 
            r.id,
            r.user_id,
            r.item_id,
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

    // Clean output buffer
    ob_clean();

    // Return clean JSON
    echo json_encode([
        'success' => true,
        'data' => array_values($reviews)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
?>