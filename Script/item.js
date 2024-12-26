document.addEventListener('DOMContentLoaded', function() {
    // Get item ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');

    if (!itemId) {
        window.location.href = 'Home.html';
        return;
    }

    // Fetch item details
    fetch(`../Controller/get_destination_detail.php?id=${itemId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();  // First get the raw text
        })
        .then(text => {
            try {
                return JSON.parse(text);  // Then try to parse it as JSON
            } catch (e) {
                console.error('Error parsing JSON:', text);
                throw new Error('Invalid JSON response');
            }
        })
        .then(data => {
            if (!data || !data.item) {
                throw new Error('Invalid data structure');
            }

            const item = data.item;
            
            // Update page title
            document.title = `${item.name} - YoXplore`;
            
            // Update basic information
            document.getElementById('itemName').textContent = item.name;
            document.getElementById('ratingScore').textContent = data.rating.average || '0.0';
            document.getElementById('totalReviews').textContent = 
                `From ${data.rating.total || '0'} users`;
            document.getElementById('itemAddress').textContent = item.address || 'Address not available';
            document.getElementById('itemHours').textContent = 
                `${item.opening_hours || '00:00'} - ${item.closing_hours || '00:00'}`;
            document.getElementById('itemPhone').textContent = item.phone || 'Not available';

            // Update map if maps_url exists
            if (item.maps_url) {
                const mapFrame = document.getElementById('mapFrame');
                const directionBtn = document.getElementById('directionBtn');
                
                if (mapFrame) {
                    mapFrame.src = item.maps_url;
                }
                
                if (directionBtn) {
                    directionBtn.onclick = () => window.open(item.maps_url, '_blank');
                }
            }

            // Update gallery images
            const gallery = document.getElementById('imageGallery');
            if (gallery && data.images && Array.isArray(data.images)) {
                // Clear existing content
                gallery.innerHTML = '';
                
                // Add new images
                data.images.forEach((imageUrl, index) => {
                    const galleryItem = document.createElement('div');
                    galleryItem.className = `gallery-item ${index === 0 ? 'parent' : 'child'}`;
                    
                    const img = document.createElement('img');
                    img.src = imageUrl;
                    img.alt = `${item.name} Image ${index + 1}`;
                    
                    // Add error handling for images
                    img.onerror = function() {
                        this.src = '../Image/placeholder.png'; // Use a placeholder image
                        this.alt = 'Image not available';
                    };
                    
                    galleryItem.appendChild(img);
                    gallery.appendChild(galleryItem);
                });
            }

            // Update reviews
            const reviewsContainer = document.getElementById('reviewsContainer');
            if (reviewsContainer && data.reviews && Array.isArray(data.reviews)) {
                // Clear existing reviews
                reviewsContainer.innerHTML = '';
                
                // Add new reviews
                data.reviews.forEach(review => {
                    const reviewCard = createReviewCard(review);
                    reviewsContainer.appendChild(reviewCard);
                });

                // Show message if no reviews
                if (data.reviews.length === 0) {
                    reviewsContainer.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to review!</p>';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching item details:', error);
            // Show user-friendly error message
            const mainContainer = document.querySelector('.main');
            if (mainContainer) {
                mainContainer.innerHTML = `
                    <div class="error-message" style="text-align: center; padding: 20px;">
                        <h2>Oops! Something went wrong</h2>
                        <p>We couldn't load the item details. Please try again later.</p>
                        <button onclick="location.reload()" style="padding: 10px 20px; margin-top: 10px;">
                            Retry
                        </button>
                    </div>
                `;
            }
        });
});

// Function to create review card
function createReviewCard(review) {
    const card = document.createElement('div');
    card.className = 'review-card';
    
    card.innerHTML = `
        <div class="review-header">
            <div class="reviewer-info">
                <img src="../Image/user.png" alt="User Profile" class="reviewer-pic">
                <div class="reviewer-details">
                    <h4>${escapeHtml(review.username || 'Anonymous')}</h4>
                    <span>${formatDate(review.created_at)}</span>
                </div>
            </div>
            <div class="review-rating">
                <div class="star-rating">
                    ${generateStars(review.rating)}
                </div>
                <span class="rating-score">${review.rating}</span>
                <span class="rating-max">/5</span>
            </div>
        </div>
        <p class="review-text">${escapeHtml(review.review_text || '')}</p>
        ${createReviewImages(review.image_urls)}
    `;
    
    return card;
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Function to format date
function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch (e) {
        return 'Date not available';
    }
}

// Function to generate star rating
function generateStars(rating) {
    const fullStars = Math.floor(rating || 0);
    const halfStar = (rating % 1) >= 0.5;
    const emptyStars = 5 - Math.ceil(rating || 0);
    
    return '★'.repeat(fullStars) +
           (halfStar ? '½' : '') +
           '☆'.repeat(emptyStars);
}

// Function to create review images
function createReviewImages(imageUrls) {
    if (!imageUrls || !Array.isArray(imageUrls) || imageUrls.length === 0) {
        return '';
    }
    
    const images = imageUrls.map(url => `
        <img src="${escapeHtml(url)}" 
             alt="Review Image"
             onerror="this.src='../Image/placeholder.png';this.alt='Image not available';">
    `).join('');
    
    return `<div class="review-images">${images}</div>`;
}

// Modal handling
const modal = document.getElementById('modal');
const openModalBtn = document.getElementById('openModal');
const closeModalBtn = document.getElementById('closeModal');

if (openModalBtn && modal) {
    openModalBtn.addEventListener('click', () => {
        modal.style.display = 'block';
    });
}

if (closeModalBtn && modal) {
    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
}

// Star rating handling in modal
const stars = document.querySelectorAll('.modal .star');
const ratingInput = document.querySelector('.modal input[name="rating"]');

if (stars && ratingInput) {
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            ratingInput.value = index + 1;
            stars.forEach((s, i) => {
                s.classList.toggle('active', i <= index);
            });
        });
    });
}

// Handle form submission
const reviewForm = document.getElementById('reviewForm');
if (reviewForm) {
    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const urlParams = new URLSearchParams(window.location.search);
        formData.append('item_id', urlParams.get('id'));
        
        try {
            const response = await fetch('../Controller/add_review.php', {
                method: 'POST',
                body: formData
            });
            
            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Error parsing response:', text);
                throw new Error('Invalid server response');
            }
            
            if (result.success) {
                alert('Review submitted successfully!');
                modal.style.display = 'none';
                location.reload();
            } else {
                alert(result.message || 'Failed to submit review');
            }
        } catch (error) {
            console.error('Error submitting review:', error);
            alert('Failed to submit review. Please try again later.');
        }
    });
}