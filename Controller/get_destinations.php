<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    // Validate and sanitize the section parameter
    $allowedSections = ['YoTrip', 'YoConcert', 'YoStay', 'YoTaste'];
    $section = $_GET['section'] ?? null;
    
    // Check if the section is valid
    if (!in_array($section, $allowedSections)) {
        throw new InvalidArgumentException('Invalid section parameter');
    }
    
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
        ORDER BY i.id DESC
    ";
    
    // Prepare and execute the statement using named parameter
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':section', $section, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert image data to base64 if needed
    $data = array_map(function($item) {
        if (!empty($item['main_image'])) {
            $item['main_image'] = base64_encode($item['main_image']);
        }
        return $item;
    }, $data);
    
    // Return JSON response
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch(InvalidArgumentException $e) {
    // Handle invalid section parameter
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);

} catch(PDOException $e) {
    // Handle database-related errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);

} catch(Exception $e) {
    // Handle any other unexpected errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

// di file ../Controller/get_destinations.php
$filter = $_GET['filter'] ?? '';
$query .= $filter ? " AND category_name = :filter" : "";
$stmt->bindParam(':filter', $filter, PDO::PARAM_STR);