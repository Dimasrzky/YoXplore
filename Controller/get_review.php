<?php
// get_reviews.php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

function getReviews($conn, $itemId, $page = 1, $perPage = 5) {
    try {
        // Validate input
        $itemId = filter_var($itemId, FILTER_VALIDATE_INT);
        $page = filter_var($page, FILTER_VALIDATE_INT);
        
        if (!$itemId || $page < 1) {
            throw new Exception('Invalid parameters');
        }

        $offset = ($page - 1) * $perPage;

        // Get reviews with user info
        $stmt = $conn->prepare("
            SELECT 
                r.id,
                r.user_id,
                r.rating,
                r.review_text,
                r.created_at,
                u.username,
                u.profile_image,
                (SELECT COUNT(*) FROM review_images WHERE review_id = r.id) as image_count
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.item_id = ?
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$itemId, $perPage, $offset]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get images for each review
        foreach ($reviews as &$review) {
            if ($review['image_count'] > 0) {
                $stmt = $conn->prepare("
                    SELECT image_url 
                    FROM review_images 
                    WHERE review_id = ?
                ");
                $stmt->execute([$review['id']]);
                $review['images'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $review['images'] = [];
            }
            unset($review['image_count']);
        }

        // Get total reviews and calculate pages
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total,
                   AVG(rating) as avg_rating
            FROM reviews 
            WHERE item_id = ?
        ");
        $stmt->execute([$itemId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'total' => (int)$stats['total'],
                'total_pages' => ceil($stats['total'] / $perPage),
                'average_rating' => round($stats['avg_rating'], 1)
            ]
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $itemId = $_GET['item_id'] ?? null;
    $page = $_GET['page'] ?? 1;
    echo json_encode(getReviews($conn, $itemId, $page));
}
?>