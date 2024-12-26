<?php
session_start();
require_once '../Config/db_connect.php';


if (!$conn) {
    die("Database connection failed");
}
// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Validasi dan sanitasi item ID
$itemId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
if (!$itemId) {
    header('Location: Home.html');
    exit;
}

try {
    // Ambil detail item dengan JOIN ke category
    $query = "
        SELECT i.*, c.name as category_name,
               COUNT(DISTINCT r.id) as total_reviews,
               COALESCE(AVG(r.rating), 0) as avg_rating
        FROM items i 
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN reviews r ON i.id = r.item_id
        WHERE i.id = :item_id AND i.is_active = 1
        GROUP BY i.id, c.name
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
    $stmt->execute();
    
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header('Location: home.php?error=item_not_found');
        exit;
    }

    // Ambil gambar item dengan pagination untuk modal
    $query = "SELECT * FROM item_images 
              WHERE item_id = :item_id 
              ORDER BY is_main DESC, display_order ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil review dengan user info dan pagination
    $query = "
        SELECT r.*, u.username, u.profile_image,
               (SELECT COUNT(*) FROM review_likes WHERE review_id = r.id) as likes_count
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.item_id = :item_id AND r.is_approved = 1
        ORDER BY r.created_at DESC
        LIMIT 10
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil gambar review dengan optimasi query
    if (!empty($reviews)) {
        $reviewIds = array_column($reviews, 'id');
        $placeholders = str_repeat('?,', count($reviewIds) - 1) . '?';
        
        $query = "SELECT review_id, image_url 
                 FROM review_images 
                 WHERE review_id IN ($placeholders)
                 ORDER BY review_id, display_order";
        $stmt = $conn->prepare($query);
        $stmt->execute($reviewIds);
        $reviewImages = $stmt->fetchAll(PDO::FETCH_GROUP);
        
        // Assign images to corresponding reviews
        foreach ($reviews as &$review) {
            $review['images'] = $reviewImages[$review['id']] ?? [];
        }
    }

    // Format waktu dengan proper time zone
    date_default_timezone_set('Asia/Jakarta');
    $openingHours = !empty($item['opening_hours']) ? date('H:i', strtotime($item['opening_hours'])) : '-';
    $closingHours = !empty($item['closing_hours']) ? date('H:i', strtotime($item['closing_hours'])) : '-';

} catch (PDOException $e) {
    error_log("Error in item.php: " . $e->getMessage());
    header('Location: error.php?type=database');
    exit;
}

// Format waktu buka-tutup
$openingHours = !empty($item['opening_hours']) ? date('H:i', strtotime($item['opening_hours'])) : '-';
$closingHours = !empty($item['closing_hours']) ? date('H:i', strtotime($item['closing_hours'])) : '-';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars(substr($item['address'], 0, 150)) ?>">
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
                                <i class='bx <?= $i <= round($item['avg_rating']) ? 'bxs-star' : 'bx-star' ?>'></i>
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

    <!-- Map Section -->
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
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="rating">
                    <input type="number" name="rating" hidden required>
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

                // Helper functions - tambahkan di bagian atas setelah try-catch
        function formatRating($rating) {
            return number_format($rating, 1);
        }

        function getStarClass($current, $rating) {
            return $current <= round($rating) ? 'bxs-star' : 'bx-star';
        }

        function formatDate($date) {
            return date('M d, Y', strtotime($date));
        }

                // Tambahkan di bagian script
        const reviewForm = document.getElementById('reviewForm');
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(reviewForm);
            
            try {
                const response = await fetch(reviewForm.action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to submit review');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to submit review');
            }
        });
    </script>
</body>
</html>