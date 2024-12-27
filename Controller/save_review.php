<?php
// File: save_review.php
header('Content-Type: application/json');
session_start();
require_once('../Config/db_connect.php');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login first');
    }

    // Get and validate input
    $userId = $_SESSION['user_id'];
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$itemId || !$rating || empty($comment)) {
        throw new Exception('All fields are required');
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
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            // Skip if there was an upload error
            if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Generate unique filename
            $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $targetPath = $uploadDir . $fileName;

            // Move uploaded file
            if (move_uploaded_file($tmpName, $targetPath)) {
                // Save image record in database
                $stmt = $conn->prepare("
                    INSERT INTO review_images (review_id, image_url)
                    VALUES (?, ?)
                ");
                $stmt->execute([$reviewId, 'uploads/reviews/' . $fileName]);
            }
        }
    }

    // Update item rating
    $stmt = $conn->prepare("
        UPDATE items 
        SET rating = (
            SELECT AVG(rating) 
            FROM reviews 
            WHERE item_id = ?
        ),
        total_reviews = (
            SELECT COUNT(*) 
            FROM reviews 
            WHERE item_id = ?
        )
        WHERE id = ?
    ");
    $stmt->execute([$itemId, $itemId, $itemId]);

    // Commit transaction
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