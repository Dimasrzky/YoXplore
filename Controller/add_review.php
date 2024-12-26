<?php
// Controller/add_review.php

require_once '../Config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false];
    
    try {
        // Get the user ID from session
        session_start();
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not logged in');
        }
        
        $userId = $_SESSION['user_id'];
        $itemId = $_POST['item_id'];
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];
        
        // Insert review
        $query = "INSERT INTO reviews (user_id, item_id, rating, comment) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiis", $userId, $itemId, $rating, $comment);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save review');
        }
        
        $reviewId = $conn->insert_id;
        
        // Handle image uploads
        if (isset($_FILES['images'])) {
            $uploadDir = '../uploads/reviews/';
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $fileName = uniqid() . '_' . $_FILES['images']['name'][$key];
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $filePath)) {
                    $query = "INSERT INTO review_images (review_id, image_url) VALUES (?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("is", $reviewId, $filePath);
                    $stmt->execute();
                }
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Review submitted successfully';
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>