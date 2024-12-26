<?php
include '../Config/db_connect.php';
header('Content-Type: application/json');
echo json_encode($itemData, JSON_PRETTY_PRINT);

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: Home.html');
    exit();
}

$id = $_GET['id'];

// Fetch item details
$query = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

// Fetch reviews for this item
$reviewQuery = "SELECT r.*, u.username 
               FROM reviews r 
               JOIN users u ON r.user_id = u.id 
               WHERE r.item_id = ?
               ORDER BY r.created_at DESC";
$reviewStmt = $conn->prepare($reviewQuery);
$reviewStmt->bind_param("i", $id);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result();

// Calculate average rating
$avgRatingQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                  FROM reviews 
                  WHERE item_id = ?";
$ratingStmt = $conn->prepare($avgRatingQuery);
$ratingStmt->bind_param("i", $id);
$ratingStmt->execute();
$ratingResult = $ratingStmt->get_result();
$ratingData = $ratingResult->fetch_assoc();

// Fetch images for this item
$imageQuery = "SELECT image_url FROM item_images WHERE item_id = ?";
$imageStmt = $conn->prepare($imageQuery);
$imageStmt->bind_param("i", $id);
$imageStmt->execute();
$images = $imageStmt->get_result();

// Convert to JSON for JavaScript use
$itemData = [
    'item' => $item,
    'reviews' => [],
    'images' => [],
    'rating' => [
        'average' => number_format($ratingData['avg_rating'], 1),
        'total' => $ratingData['total_reviews']
    ]
];

while ($review = $reviews->fetch_assoc()) {
    $itemData['reviews'][] = $review;
}

while ($image = $images->fetch_assoc()) {
    $itemData['images'][] = $image['image_url'];
}

header('Content-Type: application/json');
echo json_encode($itemData);

$conn->close();
?>