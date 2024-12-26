<?php
session_start();
require_once '../Config/database.php';

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil item ID dari URL
$itemId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$itemId) {
    header('Location: home.php');
    exit;
}

// Ambil data item
try {
    $stmt = $conn->prepare("
        SELECT i.*, 
               COUNT(r.id) as total_reviews,
               AVG(r.rating) as avg_rating
        FROM items i 
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = ?
        GROUP BY i.id
    ");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        header('Location: home.php');
        exit;
    }

    // Ambil gambar item
    $stmt = $conn->prepare("SELECT * FROM item_images WHERE item_id = ? ORDER BY is_main DESC");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Ambil review dengan user info
    $stmt = $conn->prepare("
        SELECT r.*, u.username, u.profile_image
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.item_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Ambil gambar review
    foreach ($reviews as &$review) {
        $stmt = $conn->prepare("SELECT image_url FROM review_images WHERE review_id = ?");
        $stmt->bind_param("i", $review['id']);
        $stmt->execute();
        $review['images'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

} catch (Exception $e) {
    // Log error dan redirect ke home
    error_log($e->getMessage());
    header('Location: home.php');
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
    <!-- Header/Navbar -->
    <?php include '../Components/navbar.php'; ?>

    <div class="main">
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1><?= htmlspecialchars($item['name']) ?></h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class='bx <?= $i <= $item['avg_rating'] ? 'bxs-star' : 'bx-star' ?>'></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-score"><?= number_format($item['avg_rating'], 1) ?></span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From <?= $item['total_reviews'] ?> users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p><?= htmlspecialchars($item['address']) ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p><?= $item['opening_hours'] ?> - <?= $item['closing_hours'] ?></p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p><?= htmlspecialchars($item['phone'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gallery">
                <?php foreach(array_slice($images, 0, 6) as $index => $image): ?>
                    <div class="gallery-item <?= $index === 0 ? 'parent' : 'child' ?>">
                        <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?> Image <?= $index + 1 ?>">
                    </div>
                <?php endforeach; ?>
                
                <?php if(count($images) > 6): ?>
                    <div class="gallery-item child last-item">
                        <img src="<?= htmlspecialchars($images[6]['image_url']) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?> Image 7">
                        <a href="javascript:void(0)" class="overlay-link" onclick="openGallery()">
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
                <?php foreach($images as $image): ?>
                    <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>">
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Map -->
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

    <!-- Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-header">
            <h2>Users Review</h2>
            <button class="add-review-btn" id="openModal">+ Add Review</button>
        </div>
        <div class="reviews-container">
            <?php if(empty($reviews)): ?>
                <p class="no-reviews">No reviews yet. Be the first to review!</p>
            <?php else: ?>
                <?php foreach($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <img src="<?= htmlspecialchars($review['profile_image'] ?? '../Image/user.png') ?>" 
                                     alt="User Profile" class="reviewer-pic">
                                <div class="reviewer-details">
                                    <h4><?= htmlspecialchars($review['username']) ?></h4>
                                    <span class="review-date">
                                        <?= date('M d, Y', strtotime($review['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="review-rating">
                                <div class="star-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class='bx <?= $i <= $review['rating'] ? 'bxs-star' : 'bx-star' ?>'></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-score"><?= $review['rating'] ?></span>
                                <span class="rating-max">/5</span>
                            </div>
                        </div>
                        <p class="review-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        <?php if(!empty($review['images'])): ?>
                            <div class="review-images">
                                <?php foreach($review['images'] as $image): ?>
                                    <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="Review Image">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="modal" class="modal">
        <div class="wrapper">
            <h3>Leave a Review</h3>
            <form id="reviewForm" method="POST" action="../Controller/add_review.php" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?= $itemId ?>">
                
                <div class="rating">
                    <input type="number" name="rating" hidden>
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <i class='bx bx-star star' style="--i: <?= $i ?>;"></i>
                    <?php endfor; ?>
                </div>

                <textarea name="comment" rows="4" placeholder="Write your comment here..." required></textarea>

                <div class="image-upload">
                    <label for="file-input" class="upload-label">
                        <i class='bx bx-image-add'></i> Upload Image
                    </label>
                    <input type="file" id="file-input" name="images[]" accept="image/*" multiple hidden>
                    <div id="image-preview" class="image-preview"></div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn submit">Submit</button>
                    <button type="button" class="btn cancel" id="closeModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../Components/footer.php'; ?>

    <script>
        // Simplified JavaScript for modal and review functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modal');
            const galleryModal = document.getElementById('gallery-modal');
            const openModalBtn = document.getElementById('openModal');
            const closeModalBtn = document.getElementById('closeModal');
            const stars = document.querySelectorAll('.rating .star');
            const fileInput = document.getElementById('file-input');
            const previewContainer = document.getElementById('image-preview');

            // Modal handlers
            openModalBtn.onclick = () => modal.style.display = 'flex';
            closeModalBtn.onclick = () => modal.style.display = 'none';
            
            // Gallery modal
            window.openGallery = () => galleryModal.style.display = 'block';
            document.querySelector('.close').onclick = () => galleryModal.style.display = 'none';

            // Rating stars
            stars.forEach((star, index) => {
                star.onclick = () => {
                    document.querySelector('input[name="rating"]').value = index + 1;
                    stars.forEach((s, i) => {
                        s.classList.toggle('bxs-star', i <= index);
                        s.classList.toggle('bx-star', i > index);
                    });
                };
            });

            // Image preview
            fileInput.onchange = () => {
                previewContainer.innerHTML = '';
                [...fileInput.files].forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            };

            // Close modals when clicking outside
            window.onclick = (event) => {
                if (event.target === modal) modal.style.display = 'none';
                if (event.target === galleryModal) galleryModal.style.display = 'none';
            };
        });
    </script>
</body>
</html>