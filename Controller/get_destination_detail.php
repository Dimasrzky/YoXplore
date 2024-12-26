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
   $query = "SELECT * FROM items WHERE id = ?";
   $stmt = $conn->prepare($query);
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
       throw new Exception("Item not found");
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
   $ratingQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                  FROM reviews WHERE item_id = ?";
   $ratingStmt = $conn->prepare($ratingQuery);
   
   if ($ratingStmt) {
       $ratingStmt->bind_param("i", $id);
       if ($ratingStmt->execute()) {
           $ratingResult = $ratingStmt->get_result();
           $ratingData = $ratingResult->fetch_assoc();
           $response['rating'] = [
               'average' => number_format(floatval($ratingData['avg_rating'] ?? 0), 1),
               'total' => intval($ratingData['total_reviews'] ?? 0)
           ];
       }
       $ratingStmt->close();
   }

   // Get reviews
   $reviewQuery = "SELECT r.*, u.username 
                  FROM reviews r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  WHERE r.item_id = ? 
                  ORDER BY r.created_at DESC";
   $reviewStmt = $conn->prepare($reviewQuery);
   
   if ($reviewStmt) {
       $reviewStmt->bind_param("i", $id);
       if ($reviewStmt->execute()) {
           $reviewResult = $reviewStmt->get_result();
           while ($review = $reviewResult->fetch_assoc()) {
               // Get review images
               $reviewImagesQuery = "SELECT image_url FROM review_images WHERE review_id = ?";
               $reviewImagesStmt = $conn->prepare($reviewImagesQuery);
               $reviewImages = [];
               
               if ($reviewImagesStmt) {
                   $reviewImagesStmt->bind_param("i", $review['id']);
                   if ($reviewImagesStmt->execute()) {
                       $imagesResult = $reviewImagesStmt->get_result();
                       while ($image = $imagesResult->fetch_assoc()) {
                           $reviewImages[] = $image['image_url'];
                       }
                   }
                   $reviewImagesStmt->close();
               }
               
               $response['reviews'][] = [
                   'id' => intval($review['id']),
                   'username' => $review['username'] ?? 'Anonymous',
                   'rating' => floatval($review['rating']),
                   'review_text' => $review['review_text'] ?? '',
                   'created_at' => $review['created_at'],
                   'images' => $reviewImages
               ];
           }
       }
       $reviewStmt->close();
   }

   // Get item images 
   $imageQuery = "SELECT image_url FROM item_images WHERE item_id = ?";
   $imageStmt = $conn->prepare($imageQuery);
   
   if ($imageStmt) {
       $imageStmt->bind_param("i", $id);
       if ($imageStmt->execute()) {
           $imageResult = $imageStmt->get_result();
           while ($image = $imageResult->fetch_assoc()) {
               $response['images'][] = $image['image_url'];
           }
       }
       $imageStmt->close();
   }

   // Add default image if none exists
   if (empty($response['images'])) {
       $response['images'][] = '../Image/placeholder.png';
   }

   // Send response
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