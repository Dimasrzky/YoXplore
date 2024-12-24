<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Detail</title>
    <link rel="stylesheet" href="../Style/Item.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <!-- Your existing header/navigation code -->
    </header>

    <div class="main">
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>Loading...</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <i class='bx bxs-star'></i>
                        </div>
                        <span class="rating-score">0.0</span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p>Loading address...</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p>Loading hours...</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p>Loading phone...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gallery">
                <!-- Gallery will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Photo Gallery Modal -->
    <div id="gallery-modal" class="gallery-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>All Photos</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-gallery">
                <!-- Gallery images will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-header">
            <h2>Users Review</h2>
            <button class="add-review-btn" id="openModal">+ Add Review</button>
        </div>
        <div class="reviews-container">
            <!-- Reviews will be populated by JavaScript -->
        </div>
    </div>

    <!-- Review Modal -->
    <div id="modal" class="modal">
        <div class="wrapper">
            <h3>Leave a Review</h3>
            <div class="rating">
                <input type="number" name="rating" hidden>
                <i class='bx bx-star star' style="--i: 0;"></i>
                <i class='bx bx-star star' style="--i: 1;"></i>
                <i class='bx bx-star star' style="--i: 2;"></i>
                <i class='bx bx-star star' style="--i: 3;"></i>
                <i class='bx bx-star star' style="--i: 4;"></i>
            </div>
            <textarea rows="4" placeholder="Write your comment here..."></textarea>
            <div class="image-upload">
                <label for="file-input" class="upload-label">
                    <i class='bx bx-image-add'></i> Upload Image
                </label>
                <input type="file" id="file-input" accept="image/*" hidden>
                <div id="image-preview" class="image-preview"></div>
            </div>
            <div class="btn-group">
                <button class="btn submit">Submit</button>
                <button class="btn cancel" id="closeModal">Cancel</button>
            </div>
        </div>
</div>
       <footer class="footer">
            <div class="contact-info">
                <h3>Contact us</h3>
                <ul>
                    <li><img src="../Image/footer/clock.png" alt="Clock" class="icon"> Monday - Friday, 09:00 - 17:00</li>
                    <li><img src="../Image/footer/email.png" alt="Email" class="icon"> Email: <a href="mailto:yoxplore@gmail.com">yoxplore@gmail.com</a></li>
                    <li><img src="../Image/footer/phone-call.png" alt="Phone" class="icon"> Phone: 08123456789</li>
                    <li><img src="../Image/yotrip.png" alt="Location" class="icon"> Universitas Islam Indonesia,</li>
                    <div class="alamat">
                        Gedung K.H. Mas Mansyur, <br>
                        Daerah Istimewa Yogyakarta 55584
                    </div>
                </ul>
            </div>
            
            <div class="products">
                <h3>Products</h3>
                <ul>
                    <li><a href="../Client/Yotrip.html">YoTrip</a></li>
                    <li><a href="../Client/Yotaste.html">YoTaste</a></li>
                    <li><a href="../Client/Yoconcert.html">YoConcert</a></li>
                    <li><a href="../Client/Yostay.html">YoStay</a></li>
                </ul>
            </div>
            <div class="social-media">
                <h3>Follow us on</h3>
                <ul>
                    <li class="icon"><img src="../Image/footer/sosmed/instagram.png" alt="Instagram">Instagram</li>
                    <li class="icon"><img src="../Image/footer/sosmed/tik-tok.png" alt="TikTok">TikTok</li>
                    <li class="icon"><img src="../Image/footer/sosmed/twitter.png" alt="Twitter">X</li>
                    <li class="icon"><img src="../Image/footer/sosmed/youtube.png" alt="YouTube">YouTube</li>
                    <li class="icon"><img src="../Image/footer/sosmed/facebook.png" alt="Facebook">Facebook</li>
                    <li class="icon"><img src="../Image/footer/sosmed/telegram.png" alt="Telegram">Telegram</li>
                </ul>
            </div>
            <div class="copyright">
                Copyright &copy; 2024 Yoxplore. All rights reserved
            </div>
        </footer>
    </body>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
            // Get item ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            const itemId = urlParams.get('id');

            if (!itemId) {
                showError('Item ID not found');
                return;
            }

            // Fetch item details
            fetch(`../Controller/get_item_detail.php?id=${itemId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message);
                    }

                    // Update UI with item details
                    const item = data.data.item;
                    
                    // Update title and rating
                    document.querySelector('h1').textContent = item.name;
                    document.querySelector('.rating-score').textContent = item.rating;
                    
                    // Update info
                    const addressElement = document.querySelector('.location-info p');
                    const hoursElement = document.querySelector('.info-item:nth-child(2) p');
                    const phoneElement = document.querySelector('.info-item:nth-child(3) p');

                    if (addressElement) addressElement.textContent = item.address;
                    if (hoursElement) hoursElement.textContent = item.opening_hours;
                    if (phoneElement) phoneElement.textContent = item.phone;

                    // Update gallery
                    if (item.images && item.images.length > 0) {
                        updateGallery(item.images);
                    }

                    // Update reviews if available
                    if (data.data.reviews) {
                        updateReviews(data.data.reviews);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Failed to load item details');
                });
        });

        function updateGallery(images) {
            const gallery = document.querySelector('.gallery');
            if (!gallery) return;

            // Clear existing gallery
            gallery.innerHTML = '';

            // Add main image
            if (images.length > 0) {
                const mainImageDiv = document.createElement('div');
                mainImageDiv.className = 'gallery-item parent';
                mainImageDiv.innerHTML = `<img src="${images[0]}" alt="Main Image">`;
                gallery.appendChild(mainImageDiv);
            }

            // Add thumbnail images
            images.slice(1).forEach((image, index) => {
                const thumbnailDiv = document.createElement('div');
                thumbnailDiv.className = 'gallery-item child';
                
                if (index === images.length - 2) {
                    thumbnailDiv.className += ' last-item';
                    thumbnailDiv.innerHTML = `
                        <img src="${image}" alt="Gallery Image">
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
                    `;
                } else {
                    thumbnailDiv.innerHTML = `<img src="${image}" alt="Gallery Image">`;
                }
                
                gallery.appendChild(thumbnailDiv);
            });

            // Update modal gallery
            updateModalGallery(images);
        }

        function updateModalGallery(images) {
            const modalGallery = document.querySelector('.modal-gallery');
            if (!modalGallery) return;

            modalGallery.innerHTML = images.map(image => 
                `<img src="${image}" alt="Gallery Photo">`
            ).join('');
        }

        function updateReviews(reviews) {
            const reviewsContainer = document.querySelector('.reviews-container');
            if (!reviewsContainer) return;

            reviewsContainer.innerHTML = reviews.map(review => `
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <img src="${review.profile_image || '../Image/user.png'}" alt="User Profile" class="reviewer-pic">
                            <div class="reviewer-details">
                                <h4>${review.username}</h4>
                            </div>
                        </div>
                        <div class="review-rating">
                            <div class="star-rating">
                                <i class='bx bxs-star'></i>
                            </div>
                            <span class="rating-score">${review.rating}</span>
                            <span class="rating-max">/5</span>
                        </div>
                    </div>
                    <p class="review-text">${review.review_text}</p>
                    ${review.images ? `
                        <div class="review-images">
                            ${review.images.map(image => 
                                `<img src="${image}" alt="Review Image">`
                            ).join('')}
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        function showError(message) {
            // Add error message display logic here
            console.error(message);
            // You could add a toast notification or alert box
        } 
        </script>
        <script src="../Script/item.js"></script>
</html>