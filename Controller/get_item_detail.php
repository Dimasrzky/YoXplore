<?php
class ItemController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getItem($itemId) {
        try {
            // Query untuk item details
            $query = "SELECT i.*, 
                     COUNT(DISTINCT r.id) as total_reviews,
                     COALESCE(AVG(r.rating), 0) as avg_rating
                     FROM items i 
                     LEFT JOIN reviews r ON i.id = r.item_id
                     WHERE i.id = :id
                     GROUP BY i.id";
                     
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return false;
            }

            // Get item images
            $imageQuery = "SELECT * FROM item_images WHERE item_id = :item_id ORDER BY is_main DESC";
            $imageStmt = $this->conn->prepare($imageQuery);
            $imageStmt->execute(['item_id' => $itemId]);
            $images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get reviews with user info
            $reviewQuery = "SELECT r.*, u.username, u.profile_image
                          FROM reviews r
                          JOIN users u ON r.user_id = u.id
                          WHERE r.item_id = :item_id
                          ORDER BY r.created_at DESC";
            $reviewStmt = $this->conn->prepare($reviewQuery);
            $reviewStmt->execute(['item_id' => $itemId]);
            $reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get review images if there are reviews
            if (!empty($reviews)) {
                foreach ($reviews as &$review) {
                    $imageQuery = "SELECT image_url FROM review_images WHERE review_id = :review_id";
                    $imageStmt = $this->conn->prepare($imageQuery);
                    $imageStmt->execute(['review_id' => $review['id']]);
                    $review['images'] = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            return [
                'item' => $item,
                'images' => $images,
                'reviews' => $reviews
            ];

        } catch (PDOException $e) {
            error_log("Error in ItemController: " . $e->getMessage());
            return false;
        }
    }
}