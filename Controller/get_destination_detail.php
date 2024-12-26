<?php
// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header sebelum output apapun
header('Content-Type: application/json');

try {
    // Include database connection
    require_once('../Config/db_connect.php');

    // Check if ID exists
    if (!isset($_GET['id'])) {
        throw new Exception('ID parameter is required');
    }

    $id = intval($_GET['id']);

    // Prepare the main query with error checking
    $query = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Result failed: " . $stmt->error);
    }

    $item = $result->fetch_assoc();
    
    if (!$item) {
        throw new Exception("Item not found");
    }

    // Prepare the response data
    $responseData = [
        'item' => [
            'id' => intval($item['id']),
            'name' => $item['name'],
            'category_id' => intval($item['category_id']),
            'feature_type' => $item['feature_type'],
            'address' => $item['address'],
            'opening_hours' => $item['opening_hours'],
            'closing_hours' => $item['closing_hours'],
            'phone' => $item['phone'],
            'maps_url' => $item['maps_url']
        ],
        'rating' => [
            'average' => '0.0',
            'total' => 0
        ],
        'reviews' => [],
        'images' => []
    ];

    // Get rating information
    $ratingQuery = "SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_reviews 
                   FROM reviews WHERE item_id = ?";
    $ratingStmt = $conn->prepare($ratingQuery);
    
    if ($ratingStmt) {
        $ratingStmt->bind_param("i", $id);
        if ($ratingStmt->execute()) {
            $ratingResult = $ratingStmt->get_result();
            $ratingData = $ratingResult->fetch_assoc();
            $responseData['rating']['average'] = number_format(floatval($ratingData['avg_rating']), 1);
            $responseData['rating']['total'] = intval($ratingData['total_reviews']);
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
                $responseData['reviews'][] = [
                    'id' => intval($review['id']),
                    'username' => $review['username'] ?? 'Anonymous',
                    'rating' => floatval($review['rating']),
                    'review_text' => $review['review_text'],
                    'created_at' => $review['created_at']
                ];
            }
        }
        $reviewStmt->close();
    }

    // Get images
    $imageQuery = "SELECT image_url FROM item_images WHERE item_id = ?";
    $imageStmt = $conn->prepare($imageQuery);
    
    if ($imageStmt) {
        $imageStmt->bind_param("i", $id);
        if ($imageStmt->execute()) {
            $imageResult = $imageStmt->get_result();
            while ($image = $imageResult->fetch_assoc()) {
                $responseData['images'][] = $image['image_url'];
            }
        }
        $imageStmt->close();
    }

    // If no images found, add a placeholder
    if (empty($responseData['images'])) {
        $responseData['images'][] = '../Image/placeholder.png';
    }

    // Close the main statement
    $stmt->close();
    
    // Close database connection
    $conn->close();

    // Output the JSON response
    echo json_encode($responseData);

} catch (Exception $e) {
    // Log error
    error_log("Error in get_destination_detail.php: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>