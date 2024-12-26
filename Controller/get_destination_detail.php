<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
   require_once('../Config/db_connect.php');

   if (!isset($_GET['id'])) {
       throw new Exception('ID parameter is required');
   }

   $id = intval($_GET['id']);
   
   // Get item details
   $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
   $stmt->execute([$id]);
   $item = $stmt->fetch();

   if (!$item) {
       throw new Exception("Item not found");
   }

   $response = [
       'item' => [
           'id' => intval($item['id']),
           'name' => $item['name'],
           'category_id' => intval($item['category_id']),
           'feature_type' => $item['feature_type'],
           'address' => $item['address'] ?? '',
           'opening_hours' => $item['opening_hours'] ?? '00:00',
           'closing_hours' => $item['closing_hours'] ?? '00:00',
           'phone' => $item['phone'] ?? '',
           'maps_url' => $item['maps_url'] ?? ''
       ],
       'rating' => ['average' => '0.0', 'total' => 0],
       'reviews' => [],
       'images' => []
   ];

   // Get ratings
   $ratingStmt = $conn->prepare("
       SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
       FROM reviews WHERE item_id = ?
   ");
   $ratingStmt->execute([$id]);
   $ratingData = $ratingStmt->fetch();
   
   $response['rating'] = [
       'average' => number_format(floatval($ratingData['avg_rating'] ?? 0), 1),
       'total' => intval($ratingData['total_reviews'] ?? 0)
   ];

   // Get reviews with user info
   $reviewStmt = $conn->prepare("
       SELECT r.*, u.username 
       FROM reviews r 
       LEFT JOIN client u ON r.user_id = u.id 
       WHERE r.item_id = ? 
       ORDER BY r.created_at DESC
   ");
   $reviewStmt->execute([$id]);
   
   while ($review = $reviewStmt->fetch()) {
       // Get review images
       $reviewImagesStmt = $conn->prepare("
           SELECT image_url FROM review_images WHERE review_id = ?
       ");
       $reviewImagesStmt->execute([$review['id']]);
       $reviewImages = $reviewImagesStmt->fetchAll(PDO::FETCH_COLUMN);

       $response['reviews'][] = [
           'id' => intval($review['id']),
           'username' => $review['username'] ?? 'Anonymous',
           'rating' => floatval($review['rating']),
           'review_text' => $review['review_text'] ?? '',
           'created_at' => $review['created_at'],
           'images' => $reviewImages
       ];
   }

   // Get item images
   $imageStmt = $conn->prepare("SELECT image_url FROM item_images WHERE item_id = ?");
   $imageStmt->execute([$id]);
   $response['images'] = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

   // Add default image if none exists
   if (empty($response['images'])) {
       $response['images'][] = '../Image/placeholder.png';
   }

   echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
   error_log("Error in get_destination_detail.php: " . $e->getMessage());
   http_response_code(500);
   echo json_encode([
       'error' => true, 
       'message' => $e->getMessage()
   ]);
}