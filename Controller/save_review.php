<?php
// save_review.php
header('Content-Type: application/json');
session_start();
require_once('../Config/db_connect.php');

function saveReview($conn) {
    try {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Please login first');
        }

        // Validate and sanitize input
        $userId = $_SESSION['user_id'];
        $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $reviewText = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);

        // Validate required fields
        if (!$itemId || !$rating || empty($reviewText)) {
            throw new Exception('All fields are required');
        }

        // Validate rating range
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Rating must be between 1 and 5');
        }

        $conn->beginTransaction();

        // Check if user already reviewed this item
        $stmt = $conn->prepare("
            SELECT id FROM reviews 
            WHERE user_id = ? AND item_id = ?
        ");
        $stmt->execute([$userId, $itemId]);
        if ($stmt->fetch()) {
            throw new Exception('You have already reviewed this item');
        }

        // Insert review
        $stmt = $conn->prepare("
            INSERT INTO reviews (user_id, item_id, rating, review_text, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $itemId, $rating, $reviewText]);
        $reviewId = $conn->lastInsertId();

        // Handle image uploads
        if (!empty($_FILES['images'])) {
            $uploadDir = '../uploads/reviews/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            $maxFiles = 5; // Maximum number of files

            if (count($_FILES['images']['name']) > $maxFiles) {
                throw new Exception("Maximum {$maxFiles} images allowed");
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileType = $_FILES['images']['type'][$key];
                    $fileSize = $_FILES['images']['size'][$key];

                    // Validate file type
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
                    }

                    // Validate file size
                    if ($fileSize > $maxFileSize) {
                        throw new Exception('File size too large. Maximum size is 5MB.');
                    }

                    // Generate unique filename
                    $filename = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                    $filepath = $uploadDir . $filename;

                    // Move and save file
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

        // Update item's average rating and total reviews
        $stmt = $conn->prepare("
            UPDATE items SET
            rating = (SELECT AVG(rating) FROM reviews WHERE item_id = ?),
            total_reviews = (SELECT COUNT(*) FROM reviews WHERE item_id = ?)
            WHERE id = ?
        ");
        $stmt->execute([$itemId, $itemId, $itemId]);

        $conn->commit();
        return ['success' => true];

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(saveReview($conn));
}
?>