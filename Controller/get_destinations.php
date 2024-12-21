<?php
require_once '../Config/db_connect.php';
require_once '../Config/session_check.php';

header('Content-Type: application/json');

try {
    $section = $_GET['section'] ?? '';
    
    // Debug logs
    error_log("Fetching destinations for section: " . $section);

    $sql = "SELECT i.*, c.name as category_name 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.id 
            WHERE i.feature_type = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$section]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug logs
    error_log("Found " . count($items) . " items");

    echo json_encode([
        'success' => true,
        'data' => $items
    ]);

} catch(Exception $e) {
    error_log("Error in get_destinations.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}