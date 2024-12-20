<?php
require_once '../Config/db_connect.php';

$id = $_GET['id'] ?? null;

if($id) {
    $sql = "SELECT image_url, SUBSTRING_INDEX(image_url, '.', -1) as type 
            FROM item_images WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($image) {
        header("Content-Type: image/jpeg"); // Sesuaikan dengan tipe gambar
        echo $image['image_url'];
        exit;
    }
}

// If no image found, return default image
header('Location: /Image/placeholder.jpg');