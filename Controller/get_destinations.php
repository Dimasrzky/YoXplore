<?php
require_once('../Config/db_connect.php');

$feature = $_GET['feature'] ?? '';

try {
    $stmt = $conn->prepare("
        SELECT i.*, c.name as category_name,
        (SELECT AVG(rating) FROM reviews WHERE item_id = i.id) as avg_rating
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.feature_type = ?
    ");
    $stmt->execute([$feature]);
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'destinations' => $destinations
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>