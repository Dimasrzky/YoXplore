<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $section = $_GET['section'] ?? null;
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    
    $query = "
        SELECT i.*, 
               COALESCE(im.image_url, '') as main_image,
               c.name as category_name
        FROM items i
        LEFT JOIN (
            SELECT item_id, image_url
            FROM item_images
            WHERE is_main = 1
        ) im ON i.id = im.item_id
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.feature_type = :section
    ";
    
    // Tambahkan kondisi pencarian jika ada
    if ($search) {
        $query .= " AND (i.name LIKE :search OR i.address LIKE :search)";
    }
    
    // Tambahkan filter kategori jika ada
    if ($category && $category !== 'Semua Kategori') {
        $query .= " AND c.name = :category";
    }
    
    $query .= " ORDER BY i.id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':section', $section, PDO::PARAM_STR);
    
    if ($search) {
        $searchTerm = "%{$search}%";
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    }
    
    if ($category && $category !== 'Semua Kategori') {
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Konversi gambar ke base64
    $data = array_map(function($item) {
        if (!empty($item['main_image'])) {
            $item['main_image'] = base64_encode($item['main_image']);
        }
        return $item;
    }, $data);
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>