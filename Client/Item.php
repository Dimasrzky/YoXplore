<?php
require_once('../Config/db_connect.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Detail</title>
    <link rel="stylesheet" href="../Style/item-detail.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Include your navigation header here -->
    <?php include('../Components/header.php'); ?>

    <div class="item-container">
        <!-- Header Section - Will be populated dynamically -->
        <div class="item-header">
            <h1 class="item-title"></h1>
            <div class="rating-container">
                <div class="stars">
                    <i class='bx bxs-star'></i>
                </div>
                <span class="rating"></span>
                <span class="rating-max">/5</span>
                <span class="rating-count">From users</span>
            </div>
        </div>

        <!-- Gallery Section - Will be populated dynamically -->
        <div class="gallery-container">
            <div class="main-image">
                <img src="" alt="Main View">
            </div>
            <div class="thumbnail-grid">
                <!-- Thumbnails will be added here dynamically -->
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-card">
            <div class="info-item">
                <i class='bx bx-map'></i>
                <p></p>
            </div>
            <div class="info-item">
                <i class='bx bx-time'></i>
                <p></p>
            </div>
            <div class="info-item">
                <i class='bx bx-phone'></i>
                <p></p>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <div class="reviews-header">
                <h2>Reviews</h2>
                <button class="add-review-btn" id="openReviewModal">+ Add Review</button>
            </div>
            <div class="reviews-container">
                <!-- Reviews will be added here dynamically -->
            </div>
        </div>

        <!-- Photo Modal -->
        <div class="photo-modal" id="photoModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>All Photos</h2>
                    <button class="close-modal" onclick="document.getElementById('photoModal').style.display='none'">&times;</button>
                </div>
                <div class="modal-gallery">
                    <!-- Photos will be added here dynamically -->
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div class="review-modal" id="reviewModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Write a Review</h2>
                    <button class="close-modal" onclick="document.getElementById('reviewModal').style.display='none'">&times;</button>
                </div>
                <form id="reviewForm" class="review-form">
                    <div class="rating-input">
                        <div class="stars">
                            <i class='bx bx-star' data-rating="1"></i>
                            <i class='bx bx-star' data-rating="2"></i>
                            <i class='bx bx-star' data-rating="3"></i>
                            <i class='bx bx-star' data-rating="4"></i>
                            <i class='bx bx-star' data-rating="5"></i>
                        </div>
                    </div>
                    <textarea name="review_text" placeholder="Write your review here..." required></textarea>
                    <div class="image-upload">
                        <label for="review-images">
                            <i class='bx bx-image-add'></i>
                            <span>Add Photos</span>
                        </label>
                        <input type="file" id="review-images" name="images[]" multiple accept="image/*">
                    </div>
                    <button type="submit" class="submit-review">Submit Review</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Include your footer here -->
    <?php include('../Components/footer.php'); ?>

    <script src="../Script/item.js"></script>
</body>
</html>