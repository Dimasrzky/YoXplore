<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $section = $_GET['section'] ?? null;
    $query = "
        SELECT i.*, 
               COALESCE(im.image_url, '') as main_image,
               c.name as category_name,
               COALESCE(AVG(r.rating), 0) as avg_rating
        FROM items i
        LEFT JOIN (
            SELECT item_id, image_url 
            FROM item_images 
            WHERE is_main = 1
        ) im ON i.id = im.item_id
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE 1=1
    ";
    
    // Hanya tambahkan kondisi section jika parameter ada
    if ($section) {
        $query .= " AND i.feature_type = :section";
    }
    
    // Group by untuk menghindari duplikasi karena JOIN dengan reviews
    $query .= " GROUP BY i.id";
    $query .= " ORDER BY i.id DESC";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameter hanya jika section ada
    if ($section) {
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Konversi gambar ke base64 dan format rating
    $data = array_map(function($item) {
        // Format gambar
        if (!empty($item['main_image'])) {
            $item['main_image'] = base64_encode($item['main_image']);
        }
        
        // Format rating
        $item['avg_rating'] = number_format($item['avg_rating'], 1);
        
        return $item;
    }, $items);
    
    // Generate HTML untuk setiap item
    $html = '';
    foreach ($data as $item) {
        $html .= sprintf('
            <a href="Item.html?id=%d" class="recommendation-card">
                <div class="card">
                    <div class="image-container">
                        <img src="data:image/jpeg;base64,%s" alt="%s">
                        <div class="rating">⭐ %.1f</div>
                    </div>
                    <div class="info">
                        <h3>%s</h3>
                        <p class="location">
                            <i class="location-icon"></i> %s
                        </p>
                    </div>
                </div>
            </a>
        ',
        $item['id'],
        $item['main_image'],
        htmlspecialchars($item['name']),
        $item['avg_rating'],
        htmlspecialchars($item['name']),
        htmlspecialchars($item['address'])
        );
    }
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'data' => $data  // Include raw data juga untuk debugging
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>