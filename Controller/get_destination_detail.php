<?php
header('Content-Type: application/json');
include '../Config/Connection.php';

try {
    // Check if ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('ID is required');
    }

    $id = $_GET['id'];

    // Fetch item details
    $query = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Item not found');
    }

    $item = $result->fetch_assoc();

    // Calculate average rating and total reviews
    $ratingQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                   FROM reviews 
                   WHERE item_id = ?";
    $ratingStmt = $conn->prepare($ratingQuery);
    $ratingStmt->bind_param("i", $id);
    $ratingStmt->execute();
    $ratingResult = $ratingStmt->get_result();
    $ratingData = $ratingResult->fetch_assoc();

    // Fetch reviews with user information
    $reviewQuery = "SELECT r.*, u.username 
                   FROM reviews r 
                   LEFT JOIN users u ON r.user_id = u.id 
                   WHERE r.item_id = ? 
                   ORDER BY r.created_at DESC";
    $reviewStmt = $conn->prepare($reviewQuery);
    $reviewStmt->bind_param("i", $id);
    $reviewStmt->execute();
    $reviews = $reviewStmt->get_result();

    // Initialize response data structure
    $responseData = [
        'item' => [
            'id' => $item['id'],
            'name' => $item['name'],
            'category_id' => $item['category_id'],
            'feature_type' => $item['feature_type'],
            'address' => $item['address'],
            'opening_hours' => $item['opening_hours'],
            'closing_hours' => $item['closing_hours'],
            'phone' => $item['phone'],
            'maps_url' => $item['maps_url'],
            'created_at' => $item['created_at']
        ],
        'rating' => [
            'average' => number_format($ratingData['avg_rating'] ?? 0, 1),
            'total' => (int)$ratingData['total_reviews']
        ],
        'reviews' => [],
        'images' => []
    ];

    // Add reviews to response
    while ($review = $reviews->fetch_assoc()) {
        // Fetch images for this review
        $imageQuery = "SELECT image_url FROM review_images WHERE review_id = ?";
        $imageStmt = $conn->prepare($imageQuery);
        $imageStmt->bind_param("i", $review['id']);
        $imageStmt->execute();
        $images = $imageStmt->get_result();
        
        $imageUrls = [];
        while ($image = $images->fetch_assoc()) {
            $imageUrls[] = $image['image_url'];
        }

        $responseData['reviews'][] = [
            'id' => $review['id'],
            'username' => $review['username'] ?? 'Anonymous',
            'rating' => (float)$review['rating'],
            'review_text' => $review['review_text'],
            'created_at' => $review['created_at'],
            'image_urls' => $imageUrls
        ];
    }

    // Fetch item images
    $itemImageQuery = "SELECT image_url FROM item_images WHERE item_id = ?";
    $itemImageStmt = $conn->prepare($itemImageQuery);
    $itemImageStmt->bind_param("i", $id);
    $itemImageStmt->execute();
    $itemImages = $itemImageStmt->get_result();

    while ($image = $itemImages->fetch_assoc()) {
        $responseData['images'][] = $image['image_url'];
    }

    // If no images found, add placeholder
    if (empty($responseData['images'])) {
        $responseData['images'][] = '../Image/placeholder.png';
    }

    echo json_encode($responseData, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>