// get_destination_detail.php
<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception('ID required');

    $stmt = $conn->prepare("
        SELECT i.*, c.name as category_name,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(r.id) as total_reviews
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = ?
        GROUP BY i.id
    ");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) throw new Exception('Item not found');

    $imageStmt = $conn->prepare("SELECT image_url FROM item_images WHERE item_id = ?");
    $imageStmt->execute([$id]);
    $images = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'item' => [
            'id' => $item['id'],
            'name' => $item['name'],
            'address' => $item['address'],
            'opening_hours' => $item['opening_hours'],
            'closing_hours' => $item['closing_hours'],
            'phone' => $item['phone'],
            'maps_url' => $item['maps_url'],
            'rating' => number_format($item['avg_rating'], 1),
            'total_reviews' => $item['total_reviews']
        ],
        'images' => $images ?: ['../Image/placeholder.jpg']
    ]);

} catch(Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}