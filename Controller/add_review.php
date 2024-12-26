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
    $reviewText = filter_input(INPUT_POST, 'review_text', FILTER_SANITIZE_STRING);

    if (!$itemId || !$rating || !$reviewText) {
        throw new Exception('Missing required fields');
    }

    $conn->beginTransaction();

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (user_id, item_id, rating, review_text, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $itemId, $rating, $reviewText]);
    $reviewId = $conn->lastInsertId();

    // Handle image uploads
    if (isset($_FILES['images'])) {
        $uploadDir = '../uploads/reviews/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . $_FILES['images']['name'][$key];
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($tmp_name, $filepath)) {
                    $stmt = $conn->prepare("
                        INSERT INTO review_images (review_id, image_url)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$reviewId, 'uploads/reviews/' . $filename]);
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
    echo json_encode(['success' => true]);

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