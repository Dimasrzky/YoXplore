<?php
// Controller/add_review.php

require_once '../Config/database.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add a review']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $itemId = $_POST['item_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $conn->begin_transaction();

    // Insert review
    $query = "INSERT INTO reviews (user_id, item_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $userId, $itemId, $rating, $comment);
    $stmt->execute();
    $reviewId = $conn->insert_id;

    // Handle images
    if (isset($_FILES['images'])) {
        $uploadDir = '../uploads/reviews/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmp_name, $targetPath)) {
                $query = "INSERT INTO review_images (review_id, image_url) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $reviewId, $targetPath);
                $stmt->execute();
            }
        }
    }

    // Update item rating and total reviews
    $query = "UPDATE items SET 
              rating = (SELECT AVG(rating) FROM reviews WHERE item_id = ?),
              total_reviews = (SELECT COUNT(*) FROM reviews WHERE item_id = ?)
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $itemId, $itemId, $itemId);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Review added successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>