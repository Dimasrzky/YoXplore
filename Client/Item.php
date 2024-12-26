<?php
session_start();
require_once '../Config/db_connect.php';

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Validasi ID
    if (!isset($_GET['id'])) {
        header('Location: YoTrip.php');
        exit;
    }

    $itemId = $_GET['id'];

    // Basic query untuk item
    $query = "SELECT * FROM items WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute(['id' => $itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header('Location: YoTrip.php');
        exit;
    }

    // Query untuk gambar
    $imageQuery = "SELECT * FROM item_images WHERE item_id = :item_id ORDER BY is_main DESC";
    $imageStmt = $conn->prepare($imageQuery);
    $imageStmt->execute(['item_id' => $itemId]);
    $images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk review dan rating
    $reviewQuery = "SELECT COUNT(*) as total_reviews, COALESCE(AVG(rating), 0) as avg_rating 
                   FROM reviews 
                   WHERE item_id = :item_id";
    $reviewStmt = $conn->prepare($reviewQuery);
    $reviewStmt->execute(['item_id' => $itemId]);
    $reviewData = $reviewStmt->fetch(PDO::FETCH_ASSOC);

    // Format waktu
    $openingHours = !empty($item['opening_hours']) ? date('H:i', strtotime($item['opening_hours'])) : '-';
    $closingHours = !empty($item['closing_hours']) ? date('H:i', strtotime($item['closing_hours'])) : '-';

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    header('Location: YoTrip.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['name']) ?> - YoXplore</title>
    <link rel="icon" href="../Image/Logo Yoxplore.png" type="image/png">
    <link rel="stylesheet" href="../Style/Item.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php include '../Components/navbar.php'; ?>

    <div class="main">
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1><?= htmlspecialchars($item['name']) ?></h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class='bx <?= $i <= round($reviewData['avg_rating']) ? 'bxs-star' : 'bx-star' ?>'></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-score"><?= number_format($reviewData['avg_rating'], 1) ?></span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From <?= $reviewData['total_reviews'] ?> users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p><?= htmlspecialchars($item['address']) ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p><?= $openingHours ?> - <?= $closingHours ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p><?= htmlspecialchars($item['phone'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="gallery">
                <?php if (!empty($images)): ?>
                    <!-- Main large image -->
                    <div class="gallery-item parent">
                        <img src="<?= htmlspecialchars($images[0]['image_url']) ?>" 
                             alt="Main Image">
                    </div>
                    
                    <!-- Smaller images -->
                    <?php for($i = 1; $i < min(6, count($images)); $i++): ?>
                        <div class="gallery-item child">
                            <img src="<?= htmlspecialchars($images[$i]['image_url']) ?>" 
                                 alt="Image <?= $i + 1 ?>">
                        </div>
                    <?php endfor; ?>
                    
                    <!-- Last item with overlay -->
                    <?php if(count($images) > 6): ?>
                        <div class="gallery-item child last-item">
                            <img src="<?= htmlspecialchars($images[6]['image_url']) ?>" 
                                 alt="Image 7">
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
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Map Section -->
        <?php if (!empty($item['maps_url'])): ?>
        <div class="map">
            <iframe src="<?= htmlspecialchars($item['maps_url']) ?>"
                    width="90%" height="400"
                    style="border:0; border-radius:10px;"
                    allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            <button class="route-btn" onclick="window.open('<?= htmlspecialchars($item['maps_url']) ?>', '_blank')">
                Get Direction
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Gallery Modal -->
    <div id="gallery-modal" class="gallery-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>All Photos</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-gallery">
                <?php foreach($images as $image): ?>
                    <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>">
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../Components/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('gallery-modal');
            const closeBtn = modal.querySelector('.close');
            
            window.openGallery = function() {
                modal.style.display = 'block';
            }
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>