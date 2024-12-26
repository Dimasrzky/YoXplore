<?php
session_start();
include '../Config/Connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get form data
$userId = $_SESSION['user_id'];
$itemId = $_POST['item_id'];
$rating = $_POST['rating'];
$reviewText = $_POST['review'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert review
    $reviewQuery = "INSERT INTO reviews (user_id, item_id, rating, review_text, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($reviewQuery);
    $stmt->bind_param("iiis", $userId, $itemId, $rating, $reviewText);
    $stmt->execute();
    
    $reviewId = $conn->insert_id;

    // Handle image uploads if any
    if (isset($_FILES['images'])) {
        $uploadDir = '../uploads/reviews/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $fileName = uniqid() . '_' . $_FILES['images']['name'][$key];
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($tmp_name, $filePath)) {
                // Insert image reference to database
                $imageQuery = "INSERT INTO review_images (review_id, image_url) VALUES (?, ?)";
                $imageStmt = $conn->prepare($imageQuery);
                $imageUrl = 'uploads/reviews/' . $fileName;
                $imageStmt->bind_param("is", $reviewId, $imageUrl);
                $imageStmt->execute();
            }
        }
    }

    // Update item's rating
    $updateRatingQuery = "UPDATE items i 
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
                         WHERE i.id = ?";
    $updateStmt = $conn->prepare($updateRatingQuery);
    $updateStmt->bind_param("iii", $itemId, $itemId, $itemId);
    $updateStmt->execute();

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>