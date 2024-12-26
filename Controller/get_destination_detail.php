<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once('../Config/db_connect.php');
    
    if (!isset($_GET['id'])) {
        throw new Exception('ID is required');
    }
    
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if (!$item) {
        throw new Exception('Item not found');
    }

    $response = [
        'item' => [
            'id' => $id,
            'name' => $item['name'],
            'category_id' => intval($item['category_id']),
            'feature_type' => $item['feature_type'],
            'address' => $item['address'] ?? '',
            'opening_hours' => $item['opening_hours'] ?? '00:00',
            'closing_hours' => $item['closing_hours'] ?? '00:00',
            'phone' => $item['phone'] ?? '',
            'maps_url' => $item['maps_url'] ?? ''
        ],
        'rating' => ['average' => '0.0', 'total' => 0],
        'reviews' => [],
        'images' => ['../Image/placeholder.png']
    ];

    ob_end_clean();
    echo json_encode($response);
    exit;

} catch (Exception $e) {
    ob_end_clean();
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
    exit;
}
?>