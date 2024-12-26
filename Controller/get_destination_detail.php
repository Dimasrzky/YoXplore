<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

include '../Config/db_connect.php';

try {
    // Log the request
    error_log("Received request for item details with ID: " . ($_GET['id'] ?? 'no ID provided'));

    // Check if ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('ID is required');
    }

    $id = $_GET['id'];

    // Verify database connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Fetch item details
    $query = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Get result failed: " . $stmt->error);
    }

    if ($result->num_rows === 0) {
        throw new Exception("Item not found with ID: " . $id);
    }

    $item = $result->fetch_assoc();

    // Prepare response data
    $responseData = [
        'item' => [
            'id' => $item['id'],
            'name' => $item['name'],
            'address' => $item['address'],
            'opening_hours' => $item['opening_hours'],
            'closing_hours' => $item['closing_hours'],
            'phone' => $item['phone'] ?? '',
            'maps_url' => $item['maps_url'] ?? ''
        ],
        'rating' => [
            'average' => '0.0',
            'total' => 0
        ],
        'reviews' => [],
        'images' => []
    ];

    // Add default image if none exists
    $responseData['images'][] = '../Image/placeholder.png';

    // Fetch ratings if they exist
    $ratingQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                   FROM reviews WHERE item_id = ?";
    $ratingStmt = $conn->prepare($ratingQuery);
    if ($ratingStmt) {
        $ratingStmt->bind_param("i", $id);
        $ratingStmt->execute();
        $ratingResult = $ratingStmt->get_result();
        if ($ratingResult) {
            $ratingData = $ratingResult->fetch_assoc();
            $responseData['rating']['average'] = 
                number_format($ratingData['avg_rating'] ?? 0, 1);
            $responseData['rating']['total'] = 
                (int)($ratingData['total_reviews'] ?? 0);
        }
    }

    // Log the response data for debugging
    error_log("Response data: " . json_encode($responseData));

    // Send the response
    echo json_encode($responseData);

} catch (Exception $e) {
    // Log the error
    error_log("Error in get_destination_detail.php: " . $e->getMessage());
    
    // Send error response
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>