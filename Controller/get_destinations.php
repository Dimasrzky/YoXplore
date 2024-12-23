// Controller/get_destinations.php
<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $query = "SELECT i.*, img.image_url as main_image 
              FROM items i 
              LEFT JOIN (
                  SELECT item_id, image_url 
                  FROM item_images 
                  WHERE is_main = 1
              ) img ON i.id = img.item_id 
              ORDER BY i.id DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate HTML for each item
    $html = '';
    foreach ($items as $item) {
        $html .= sprintf('
            <a href="Item.html?id=%d" class="recommendation-item">
                <div class="item-card">
                    <img src="data:image/jpeg;base64,%s" alt="%s">
                    <div class="item-info">
                        <h3>%s</h3>
                        <p class="location">
                            <i class="location-icon"></i>
                            %s
                        </p>
                    </div>
                </div>
            </a>
        ',
        $item['id'],
        base64_encode($item['main_image']),
        htmlspecialchars($item['name']),
        htmlspecialchars($item['name']),
        htmlspecialchars($item['address'])
        );
    }

    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>