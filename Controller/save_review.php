<?php
// save_review.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();
require_once('../Config/db_connect.php');

try {
    // Debug: Log incoming data
    error_log("Review submission started");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login first');
    }

    // Get and validate input
    $userId = $_SESSION['user_id'];
    $itemId = isset($_POST['item_id']) ? filter_var($_POST['item_id'], FILTER_VALIDATE_INT) : null;
    $rating = isset($_POST['rating']) ? filter_var($_POST['rating'], FILTER_VALIDATE_INT) : null;
    $reviewText = isset($_POST['comment']) ? trim($_POST['comment']) : null;

    // Debug: Log processed input
    error_log("Processed input - userId: $userId, itemId: $itemId, rating: $rating, reviewText: $reviewText");

    // Validate required fields
    if (!$itemId || !$rating || empty($reviewText)) {
        throw new Exception('All fields are required. Please fill in all information.');
    }

    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating must be between 1 and 5');
    }

    // Start transaction
    $conn->beginTransaction();
    error_log("Transaction started");

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (user_id, item_id, rating, review_text, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    $result = $stmt->execute([$userId, $itemId, $rating, $reviewText]);
    
    if (!$result) {
        throw new Exception('Failed to insert review');
    }

    $reviewId = $conn->lastInsertId();
    error_log("Review inserted with ID: $reviewId");

    // Handle image uploads if any
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = '../uploads/reviews/';
        
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
                    error_log("Image uploaded: $fileName");
                }
            }
        }
    }

    // Update item's average rating
    $stmt = $conn->prepare("
        UPDATE items SET 
        rating = (SELECT AVG(rating) FROM reviews WHERE item_id = ?),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE item_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$itemId, $itemId, $itemId]);

    $conn->commit();
    error_log("Transaction committed successfully");

    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in review submission: " . $e->getMessage());
    
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>