<?php
session_start();
require_once '../Config/db_connect.php';

// Validasi ID
$itemId = isset($_GET['id']) ? $_GET['id'] : null;

try {
    // Query untuk mengambil detail item
    $query = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item not found");
    }

    // Query untuk gambar
    $imageQuery = "SELECT * FROM item_images WHERE item_id = ?";
    $imageStmt = $conn->prepare($imageQuery);
    $imageStmt->execute([$itemId]);
    $images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format waktu
    date_default_timezone_set('Asia/Jakarta');
    $openingHours = !empty($item['opening_hours']) ? date('H:i', strtotime($item['opening_hours'])) : '-';
    $closingHours = !empty($item['closing_hours']) ? date('H:i', strtotime($item['closing_hours'])) : '-';

} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: home.php?error=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $item['name'] ?> - YoXplore</title>
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
                    <h1><?= $item['name'] ?></h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <i class='bx bxs-star'></i>
                        </div>
                        <span class="rating-score"><?= number_format($item['rating'], 1) ?></span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p><?= $item['address'] ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p><?= $openingHours ?> - <?= $closingHours ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p><?= $item['phone'] ?: '-' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="gallery">
                <?php if (!empty($images)): ?>
                    <div class="gallery-item parent">
                        <img src="<?= $images[0]['image_url'] ?>" alt="Main Image">
                    </div>
                    
                    <?php for($i = 1; $i < min(6, count($images)); $i++): ?>
                        <div class="gallery-item child">
                            <img src="<?= $images[$i]['image_url'] ?>" alt="Image <?= $i + 1 ?>">
                        </div>
                    <?php endfor; ?>
                    
                    <?php if(count($images) > 6): ?>
                        <div class="gallery-item child last-item">
                            <img src="<?= $images[6]['image_url'] ?>" alt="Image 7">
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
            <iframe 
                src="<?= $item['maps_url'] ?>"
                width="90%" 
                height="400" 
                style="border:0; border-radius:10px;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            <button class="route-btn" onclick="window.open('<?= $item['maps_url'] ?>', '_blank')">
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
                    <img src="<?= $image['image_url'] ?>" alt="<?= $item['name'] ?>">
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