<?php
// Controller/get_item_detail.php

require_once '../Config/database.php';

function getItemDetail($id) {
    global $conn;
    
    // Get main item details
    $query = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if (!$item) {
        return ['success' => false, 'message' => 'Item not found'];
    }
    
    // Get images
    $query = "SELECT image_url, is_main FROM item_images WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    // Get reviews with user info
    $query = "SELECT r.*, u.username, u.profile_image 
              FROM reviews r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        // Get review images
        $query = "SELECT image_url FROM review_images WHERE review_id = ?";
        $stmt2 = $conn->prepare($query);
        $stmt2->bind_param("i", $row['id']);
        $stmt2->execute();
        $images_result = $stmt2->get_result();
        $review_images = [];
        while ($img = $images_result->fetch_assoc()) {
            $review_images[] = $img['image_url'];
        }
        $row['images'] = $review_images;
        $reviews[] = $row;
    }
    
    return [
        'success' => true,
        'item' => $item,
        'images' => $images,
        'reviews' => $reviews
    ];
}

if (isset($_GET['id'])) {
    $response = getItemDetail($_GET['id']);
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>