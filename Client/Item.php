<?php
session_start();
require_once '../Config/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Validate item ID
$itemId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
if (!$itemId) {
    header('Location: home.php');
    exit;
}

try {
    // Get item details
    $query = "SELECT i.*, 
              COUNT(DISTINCT r.id) as total_reviews,
              COALESCE(AVG(r.rating), 0) as avg_rating
              FROM items i 
              LEFT JOIN reviews r ON i.id = r.item_id
              WHERE i.id = :id
              GROUP BY i.id";
    $stmt = $conn->prepare($query);
    $stmt->execute(['id' => $itemId]);
    $data['item'] = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data['item']) {
        header('Location: home.php');
        exit;
    }

    // Get images
    $query = "SELECT * FROM item_images WHERE item_id = :item_id ORDER BY is_main DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute(['item_id' => $itemId]);
    $data['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format time
    date_default_timezone_set('Asia/Jakarta');
    $openingHours = !empty($data['item']['opening_hours']) ? 
        date('H:i', strtotime($data['item']['opening_hours'])) : '-';
    $closingHours = !empty($data['item']['closing_hours']) ? 
        date('H:i', strtotime($data['item']['closing_hours'])) : '-';

} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Location: home.php?error=database');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['item']['name']) ?> - YoXplore</title>
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
                    <h1><?= htmlspecialchars($data['item']['name']) ?></h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <i class='bx bxs-star'></i>
                        </div>
                        <span class="rating-score"><?= number_format($data['item']['avg_rating'], 1) ?></span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From <?= $data['item']['total_reviews'] ?> users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p><?= htmlspecialchars($data['item']['address']) ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p><?= $openingHours ?> - <?= $closingHours ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p><?= htmlspecialchars($data['item']['phone'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="gallery">
                <?php if (!empty($data['images'])): ?>
                    <!-- Main large image -->
                    <div class="gallery-item parent">
                        <img src="<?= htmlspecialchars($data['images'][0]['image_url']) ?>" 
                             alt="Main Image">
                    </div>
                    
                    <!-- Smaller images -->
                    <?php for($i = 1; $i < min(6, count($data['images'])); $i++): ?>
                        <div class="gallery-item child">
                            <img src="<?= htmlspecialchars($data['images'][$i]['image_url']) ?>" 
                                 alt="Image <?= $i + 1 ?>">
                        </div>
                    <?php endfor; ?>
                    
                    <!-- Last item with overlay -->
                    <?php if(count($data['images']) > 6): ?>
                        <div class="gallery-item child last-item">
                            <img src="<?= htmlspecialchars($data['images'][6]['image_url']) ?>" 
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
    </div>

    <!-- Gallery Modal -->
    <div id="gallery-modal" class="gallery-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>All Photos</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-gallery">
                <?php foreach($data['images'] as $image): ?>
                    <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                         alt="<?= htmlspecialchars($data['item']['name']) ?>">
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