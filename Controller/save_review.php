<?php
header('Content-Type: application/json');
session_start();
require_once('../Config/db_connect.php');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login first');
    }

    $userId = $_SESSION['user_id'];
    $itemId = $_POST['item_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Validate inputs
    if (!$itemId || !$rating || empty($comment)) {
        throw new Exception('All fields are required');
    }

    // Check if user already submitted a review recently
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM reviews 
        WHERE user_id = ? 
        AND item_id = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ");
    $stmt->execute([$userId, $itemId]);
    $recentReviews = $stmt->fetchColumn();

    if ($recentReviews > 0) {
        throw new Exception('Please wait a moment before submitting another review');
    }

    // Start transaction
    $conn->beginTransaction();

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (user_id, item_id, rating, review_text, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $itemId, $rating, $comment]);
    $reviewId = $conn->lastInsertId();

    // Handle image uploads
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = '../uploads/reviews/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $stmt = $conn->prepare("
                        INSERT INTO review_images (review_id, image_url)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$reviewId, 'uploads/reviews/' . $fileName]);
                }
            }
        }
    }

    // Update item rating
    $stmt = $conn->prepare("
        UPDATE items SET
        rating = (SELECT AVG(rating) FROM reviews WHERE item_id = ?),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE item_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$itemId, $itemId, $itemId]);

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>