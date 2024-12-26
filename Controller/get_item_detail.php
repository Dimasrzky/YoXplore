<?php
// Controller/get_item_detail.php

require_once '../Config/database.php';

function getItemDetail($id) {
    global $conn;
    
    try {
        // Get item details with proper time formatting
        $query = "SELECT 
                    id, name, address, 
                    TIME_FORMAT(opening_hours, '%H:%i') as opening_hours,
                    TIME_FORMAT(closing_hours, '%H:%i') as closing_hours,
                    phone, rating, total_reviews, 
                    maps_url, feature_type
                 FROM items WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        // Get item images
        $query = "SELECT image_url, is_main 
                 FROM item_images 
                 WHERE item_id = ? 
                 ORDER BY is_main DESC";
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
                 WHERE r.item_id = ? 
                 ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            // Get review images
            $query2 = "SELECT image_url 
                      FROM review_images 
                      WHERE review_id = ?";
            $stmt2 = $conn->prepare($query2);
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
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

header('Content-Type: application/json');
if (isset($_GET['id'])) {
    echo json_encode(getItemDetail($_GET['id']));
} else {
    echo json_encode(['success' => false, 'message' => 'No item ID provided']);
}
?>