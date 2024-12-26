<?php
// Prevent any output before headers
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    // Include connection
    require_once('../Config/Connection.php');

    // Check if ID exists
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        throw new Exception('Invalid ID provided');
    }

    // Fetch item details
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
        throw new Exception('Item not found');
    }

    // Initialize response array
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
        'rating' => [
            'average' => '0.0',
            'total' => 0
        ],
        'reviews' => [],
        'images' => []
    ];

    // Get rating info
    $ratingQuery = "SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_reviews 
                   FROM reviews WHERE item_id = ?";
    $ratingStmt = $conn->prepare($ratingQuery);
    if ($ratingStmt && $ratingStmt->execute([$id])) {
        $ratingData = $ratingStmt->get_result()->fetch_assoc();
        $response['rating'] = [
            'average' => number_format(floatval($ratingData['avg_rating']), 1),
            'total' => intval($ratingData['total_reviews'])
        ];
    }

    // Get reviews
    $reviewQuery = "SELECT r.*, u.username 
                   FROM reviews r 
                   LEFT JOIN users u ON r.user_id = u.id 
                   WHERE r.item_id = ? 
                   ORDER BY r.created_at DESC 
                   LIMIT 10";
    $reviewStmt = $conn->prepare($reviewQuery);
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

    // Get images
    $imageQuery = "SELECT image_url FROM item_images WHERE item_id = ?";
    $imageStmt = $conn->prepare($imageQuery);
    if ($imageStmt && $imageStmt->execute([$id])) {
        $images = $imageStmt->get_result();
        while ($image = $images->fetch_assoc()) {
            $response['images'][] = $image['image_url'];
        }
    }

    // Add default image if none exists
    if (empty($response['images'])) {
        $response['images'][] = '../Image/placeholder.png';
    }

    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Send response
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;

} catch (Exception $e) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Log error
    error_log("Error in get_destination_detail.php: " . $e->getMessage());

    // Send error response
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>