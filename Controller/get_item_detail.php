<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    // Validate item ID
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid item ID');
    }

    // Get item details with category info
    $query = "
        SELECT 
            i.*,
            c.name as category_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.id = :id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception('Item not found');
    }

    // Get item images
    $imageQuery = "
        SELECT image_url
        FROM item_images
        WHERE item_id = :item_id
        ORDER BY is_main DESC
    ";
    
    $imageStmt = $conn->prepare($imageQuery);
    $imageStmt->execute(['item_id' => $id]);
    $images = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

    // Get reviews with user info and images
    $reviewQuery = "
        SELECT 
            r.*,
            u.username,
            u.profile_image,
            GROUP_CONCAT(ri.image_url) as review_images
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN review_images ri ON r.review_id = ri.id
        WHERE r.item_id = :item_id
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ";
    
    $reviewStmt = $conn->prepare($reviewQuery);
    $reviewStmt->execute(['item_id' => $id]);
    $reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

    // Process reviews
    foreach ($reviews as &$review) {
        // Convert review images string to array
        $review['images'] = $review['review_images'] ? explode(',', $review['review_images']) : [];
        unset($review['review_images']);
        
        // Set default profile image if none
        $review['profile_image'] = $review['profile_image'] ?: '../Image/user.png';
        
        // Format created_at date
        $review['created_at'] = date('d M Y', strtotime($review['created_at']));
    }

    // Calculate average rating
    $avgRating = 0;
    $totalReviews = count($reviews);
    if ($totalReviews > 0) {
        $ratingSum = array_sum(array_column($reviews, 'rating'));
        $avgRating = round($ratingSum / $totalReviews, 1);
    }

    // Format response
    $response = [
        'success' => true,
        'data' => [
            'id' => $item['id'],
            'name' => $item['name'],
            'category_id' => $item['category_id'],
            'category_name' => $item['category_name'],
            'feature_type' => $item['feature_type'],
            'address' => $item['address'],
            'opening_hours' => $item['opening_hours'],
            'closing_hours' => $item['closing_hours'],
            'phone' => $item['phone'],
            'maps_url' => $item['maps_url'],
            'rating' => $avgRating,
            'total_reviews' => $totalReviews,
            'created_at' => $item['created_at'],
            'images' => $images ?: [],
            'reviews' => $reviews
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}