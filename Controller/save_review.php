// Controller/save_review.php
<?php
session_start();
require_once('../Config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    try {
        $conn->beginTransaction();

        // Simpan review
        $stmt = $conn->prepare("
            INSERT INTO reviews (item_id, user_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$item_id, $user_id, $rating, $comment]);
        $review_id = $conn->lastInsertId();

        // Upload dan simpan gambar review jika ada
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['images']['name'][$key];
                $file_path = "../uploads/reviews/" . time() . "_" . $file_name;
                
                move_uploaded_file($tmp_name, $file_path);
                
                $stmt = $conn->prepare("
                    INSERT INTO review_images (review_id, image_url)
                    VALUES (?, ?)
                ");
                $stmt->execute([$review_id, $file_path]);
            }
        }

        // Update rating rata-rata item
        $stmt = $conn->prepare("
            UPDATE items i
            SET rating = (
                SELECT AVG(rating)
                FROM reviews
                WHERE item_id = ?
            )
            WHERE id = ?
        ");
        $stmt->execute([$item_id, $item_id]);

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>