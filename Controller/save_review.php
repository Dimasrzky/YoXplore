<?php
// Prevent any output before JSON
ob_start();

header('Content-Type: application/json');
session_start();
require_once('../Config/db_connect.php');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login first');
    }

    $userId = $_SESSION['user_id'];
    $itemId = $_POST['item_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? null;

    // Validate inputs
    if (!$itemId || !$rating || !$comment) {
        throw new Exception('All fields are required');
    }

    // Clear any previous output
    ob_clean();

    $conn->beginTransaction();

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (user_id, item_id, rating, review_text, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $success = $stmt->execute([$userId, $itemId, $rating, $comment]);
    
    if (!$success) {
        throw new Exception('Failed to save review');
    }

    $reviewId = $conn->lastInsertId();

    // Handle images if any
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../uploads/reviews/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                    $stmt = $conn->prepare("
                        INSERT INTO review_images (review_id, image_url)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$reviewId, 'uploads/reviews/' . $fileName]);
                }
            }
        }
    }

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

// End output buffer
ob_end_flush();
?>