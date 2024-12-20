// Controller/add_destination.php
<?php
require_once('../Config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $openTime = $_POST['openTime'];
    $feature_type = $_POST['feature_type']; // YoTrip/YoConcert/dll
    $category_id = $_POST['category_id'];
    
    // Handle image upload
    $image = $_FILES['image'];
    $image_path = '../uploads/destinations/' . time() . '_' . $image['name'];
    move_uploaded_file($image['tmp_name'], $image_path);

    try {
        $stmt = $conn->prepare("
            INSERT INTO items (name, category_id, feature_type, address, opening_hours, main_image) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category_id, $feature_type, $address, $openTime, $image_path]);

        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>