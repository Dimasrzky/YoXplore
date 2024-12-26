<?php
include '../Config/db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get a test item ID
$query = "SELECT id FROM items LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $testId = $row['id'];
    
    // Now test the full query
    $itemQuery = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($itemQuery);
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $itemResult = $stmt->get_result();
    $item = $itemResult->fetch_assoc();
    
    // Print the result
    echo "Test Results:\n";
    echo "Item found: " . ($item ? "Yes" : "No") . "\n";
    if ($item) {
        echo "Item details:\n";
        print_r($item);
    }
} else {
    echo "No items found in database";
}

$conn->close();
?>