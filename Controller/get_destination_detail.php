<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
   require_once('../Config/db_connect.php');

   if (!isset($_GET['id'])) {
       throw new Exception('ID parameter is required');
   }

   $id = intval($_GET['id']);
   
   // Get item details
   $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
   if (!$stmt) {
       throw new Exception($conn->error);
   }
   
   $stmt->bind_param("i", $id);
   if (!$stmt->execute()) {
       throw new Exception($stmt->error);
   }

   $result = $stmt->get_result();
   $item = $result->fetch_assoc();

   if (!$item) {
       throw new Exception("Item not found with ID: $id");
   }

   // Initialize response
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
       SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_reviews 
       FROM reviews WHERE item_id = ?
   ");
   
   if ($ratingStmt && $ratingStmt->execute([$id])) {
       $ratingData = $ratingStmt->get_result()->fetch_assoc();
       $response['rating'] = [
           'average' => number_format(floatval($ratingData['avg_rating']), 1),
           'total' => intval($ratingData['total_reviews'])
       ];
   }

   // Get reviews with usernames
   $reviewStmt = $conn->prepare("
       SELECT r.*, u.username 
       FROM reviews r 
       LEFT JOIN users u ON r.user_id = u.id 
       WHERE r.item_id = ? 
       ORDER BY r.created_at DESC
   ");
   
   if ($reviewStmt && $reviewStmt->execute([$id])) {
       $reviews = $reviewStmt->get_result();
       while ($review = $reviews->fetch_assoc()) {
           $response['reviews'][] = [
               'id' => intval($review['id']),
               'username' => $review['username'] ?? 'Anonymous',
               'rating' => floatval($review['rating']),
               'review_text' => $review['review_text'] ?? '',
               'created_at' => $review['created_at']
           ];
       }
   }

   // Get item images
   $imageStmt = $conn->prepare("SELECT image_url FROM item_images WHERE item_id = ?");
   if ($imageStmt && $imageStmt->execute([$id])) {
       $images = $imageStmt->get_result();
       while ($image = $images->fetch_assoc()) {
           $response['images'][] = $image['image_url'];
       }
   }

   // Add default image if none exists
   if (empty($response['images'])) {
       $response['images'][] = '/YoXplore/Image/placeholder.png';
   }

   echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
   error_log("Error in get_destination_detail.php: " . $e->getMessage());
   http_response_code(500);
   echo json_encode([
       'error' => true,
       'message' => $e->getMessage()
   ]);
} finally {
   if (isset($conn)) {
       $conn->close();
   }
}