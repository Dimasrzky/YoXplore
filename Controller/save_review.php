<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    // Validasi input
    $item_id = $_POST['item_id'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? null;
    
    if (!$item_id || !$user_id || !$rating) {
        throw new Exception('Data tidak lengkap');
    }

    // Mulai transaksi
    $conn->beginTransaction();

    // Simpan review
    $query = "
        INSERT INTO reviews (item_id, user_id, rating, comment)
        VALUES (:item_id, :user_id, :rating, :comment)
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':comment', $comment);
    $stmt->execute();
    
    $review_id = $conn->lastInsertId();

    // Upload dan simpan gambar review jika ada
    if (!empty($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $image_data = file_get_contents($tmp_name);
            
            $query = "
                INSERT INTO review_images (review_id, image_url)
                VALUES (:review_id, :image_url)
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':review_id', $review_id);
            $stmt->bindParam(':image_url', $image_data);
            $stmt->execute();
        }
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Review berhasil disimpan'
    ]);

} catch(Exception $e) {
    if ($conn) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>