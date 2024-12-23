<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak ditemukan');
    }

    // Get main item details
    $stmt = $conn->prepare("
        SELECT i.*, 
               c.name as category_name,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.id) as review_count
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = ?
        GROUP BY i.id
    ");
    
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        throw new Exception('Item tidak ditemukan');
    }

    // Get all images for this item
    $stmt = $conn->prepare("
        SELECT image_url, is_main
        FROM item_images
        WHERE item_id = ?
        ORDER BY is_main DESC, id ASC
    ");
    $stmt->execute([$_GET['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get reviews with user info
    $stmt = $conn->prepare("
        SELECT 
            r.*,
            u.username,
            u.profile_image,
            GROUP_CONCAT(ri.image_url) as review_images
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN review_images ri ON r.id = ri.review_id
        WHERE r.item_id = ?
        GROUP BY r.id
        ORDER BY r.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$_GET['id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate HTML
    $html = '
    <div class="main">
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>' . htmlspecialchars($item['name']) . '</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <i class="bx bxs-star"></i>
                        </div>
                        <span class="rating-score">' . number_format($item['avg_rating'], 1) . '</span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p>' . htmlspecialchars($item['address']) . '</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p>' . htmlspecialchars($item['opening_hours']) . '</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p>' . htmlspecialchars($item['phone']) . '</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gallery">';

    // Add images to gallery
    foreach ($images as $index => $image) {
        if ($index === 0) {
            $html .= '
                <div class="gallery-item parent">
                    <img src="data:image/jpeg;base64,' . base64_encode($image['image_url']) . '" alt="Main Image">
                </div>';
        } elseif ($index < 6) {
            $html .= '
                <div class="gallery-item child">
                    <img src="data:image/jpeg;base64,' . base64_encode($image['image_url']) . '" alt="Gallery Image ' . $index . '">
                </div>';
        }
    }

    // Add "See All Photos" overlay
    $html .= '
            <div class="gallery-item child last-item">
                <img src="data:image/jpeg;base64,' . base64_encode(end($images)['image_url']) . '" alt="Last Image">
                <a href="javascript:void(0)" onclick="openGallery()" class="overlay-link">
                    <div class="overlay-content">
                        <div class="image-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke="white" stroke-width="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5" fill="white"/>
                                <path d="M3 15L8 10L13 15L21 7" stroke="white" stroke-width="2"/>
                            </svg>
                        </div>
                        <span>See All Photos</span>
                    </div>
                </a>
            </div>
            </div>
        </div>
    </div>';

    // Add map section
    $html .= '
    <div class="map">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.115726227853!2d' . $item['longitude'] . '!3d' . $item['latitude'] . '!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0:0x0!2zM!5e0!3m2!1sen!2sid!4v1624442830000!5m2!1sen!2sid"
            width="90%" 
            height="400" 
            style="border:0; border-radius:10px;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        <button class="route-btn" onclick="window.open(\'https://www.google.com/maps/dir//' . $item['latitude'] . ',' . $item['longitude'] . '\', \'_blank\')">Get Direction</button>
    </div>';

    // Add reviews section
    $html .= '
    <div class="reviews-section">
        <div class="reviews-header">
            <h2>Users Review</h2>
            <button class="add-review-btn" id="openModal">+ Add Review</button>
        </div>
        <div class="reviews-container">';

    // Add review cards
    foreach ($reviews as $review) {
        $html .= '
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <img src="' . (!empty($review['profile_image']) ? 'data:image/jpeg;base64,' . base64_encode($review['profile_image']) : '../Image/user.png') . '" alt="User Profile" class="reviewer-pic">
                        <div class="reviewer-details">
                            <h4>' . htmlspecialchars($review['username']) . '</h4>
                        </div>
                    </div>
                    <div class="review-rating">
                        <div class="star-rating">
                            <i class="bx bxs-star"></i>
                        </div>
                        <span class="rating-score">' . $review['rating'] . '</span>
                        <span class="rating-max">/5</span>
                    </div>
                </div>
                <p class="review-text">' . htmlspecialchars($review['comment']) . '</p>';

        // Add review images if any
        if (!empty($review['review_images'])) {
            $reviewImages = explode(',', $review['review_images']);
            $html .= '<div class="review-images">';
            foreach ($reviewImages as $img) {
                $html .= '<img src="data:image/jpeg;base64,' . base64_encode($img) . '" alt="Review Image">';
            }
            $html .= '</div>';
        }

        $html .= '</div>';
    }

    $html .= '
        </div>
    </div>';

    echo json_encode([
        'success' => true,
        'html' => $html,
        'data' => [
            'item' => $item,
            'images' => array_map(function($img) {
                $img['image_url'] = base64_encode($img['image_url']);
                return $img;
            }, $images),
            'reviews' => $reviews
        ]
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>