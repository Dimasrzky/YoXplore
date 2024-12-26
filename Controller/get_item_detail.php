<?php
require_once '../Config/database.php';

// Pastikan output header JSON di awal file
header('Content-Type: application/json');
error_reporting(0); // Nonaktifkan error reporting PHP

function getItemDetail($id) {
    global $conn;
    
    try {
        // Get item details
        $query = "SELECT * FROM items WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        if (!$item) {
            return json_encode(['success' => false, 'message' => 'Item not found']);
        }
        
        // Get item images
        $query = "SELECT * FROM item_images WHERE item_id = ? ORDER BY is_main DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $images = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        
        $response = [
            'success' => true,
            'data' => [
                'item' => $item,
                'images' => $images,
                'reviews' => [] // Sementara kosong karena tabel reviews masih kosong
            ]
        ];
        
        return json_encode($response);
        
    } catch (Exception $e) {
        return json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// Pastikan request memiliki ID
if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No item ID provided'
    ]);
    exit;
}

echo getItemDetail($_GET['id']);
?>