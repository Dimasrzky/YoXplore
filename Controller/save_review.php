<?php
// Controller/save_review.php
header('Content-Type: application/json');
session_start();
require_once('../Config/db_connect.php');

try {

    $checkStmt = $conn->prepare("
        SELECT id FROM reviews 
        WHERE user_id = ? AND item_id = ?
    ");
    $checkStmt->execute([$userId, $itemId]);
    
    if ($checkStmt->fetch()) {
        throw new Exception('You have already reviewed this item');
    }
    
    $stmt->execute([$userId, $itemId, $rating, $comment]);
    // Debug
    error_log("POST data: " . print_r($_POST, true));
    error_log("Session data: " . print_r($_SESSION, true));

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login first');
    }

    $userId = $_SESSION['user_id'];
    $itemId = $_POST['item_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment']; // Changed from review_text to comment

    if (!$itemId || !$rating || !$comment) {
        throw new Exception('All fields are required');
    }

    $conn->beginTransaction();

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (user_id, item_id, rating, review_text, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([$userId, $itemId, $rating, $comment]);
    
    if (!$result) {
        throw new Exception('Failed to save review');
    }

    // Update average rating
    $stmt = $conn->prepare("
        UPDATE items 
        SET rating = (
            SELECT AVG(rating) 
            FROM reviews 
            WHERE item_id = ?
        )
        WHERE id = ?
    ");
    $stmt->execute([$itemId, $itemId]);

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Review saved successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error saving review: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>