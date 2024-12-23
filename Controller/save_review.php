// Controller/save_review.php
<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_POST['item_id']) || !isset($_POST['rating']) || !isset($_POST['comment'])) {
        throw new Exception('Missing required fields');
    }

    $conn->beginTransaction();

    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (item_id, user_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['item_id'],
        $_SESSION['user_id'],
        $_POST['rating'],
        $_POST['comment']
    ]);
    $reviewId = $conn->lastInsertId();

    // Handle images if any
    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $imageData = file_get_contents($tmp_name);
            $stmt = $conn->prepare("
                INSERT INTO review_images (review_id, image_url)
                VALUES (?, ?)
            ");
            $stmt->execute([$reviewId, $imageData]);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>