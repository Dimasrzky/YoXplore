<?php
header('Content-Type: application/json');
session_start();
require_once('../Config/db_connect.php');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login first');
    }

    $userId = $_SESSION['user_id'];
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = $_POST['comment']; // Get raw comment data

    // Validate inputs
    if (!$itemId || !$rating || empty($comment)) {
        throw new Exception('All fields are required');
    }

    // Check for existing review
    $checkStmt = $conn->prepare("
        SELECT id FROM reviews 
        WHERE user_id = ? AND item_id = ?
    ");
    $checkStmt->execute([$userId, $itemId]);
    
    if ($checkStmt->fetch()) {
        throw new Exception('You have already reviewed this item');
    }

    $conn->beginTransaction();

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (user_id, item_id, rating, review_text, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$userId, $itemId, $rating, $comment]);

    // Update item rating
    $stmt = $conn->prepare("
        UPDATE items SET
        rating = (SELECT AVG(rating) FROM reviews WHERE item_id = ?),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE item_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$itemId, $itemId, $itemId]);

    $conn->commit();
    
    // Return clean JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    // Return clean JSON error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>