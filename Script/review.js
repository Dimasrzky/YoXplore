document.addEventListener('DOMContentLoaded', function() {
    // Get item_id from URL
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');

    // Load item data
    if (itemId) {
        fetchItemDetails(itemId);
    }

    // Initialize IntersectionObserver only if element exists
    const popularGrid = document.querySelector('.popular-grid');
    if (popularGrid) {
        initializeIntersectionObserver(popularGrid);
    }
});

// Fetch item details
async function fetchItemDetails(itemId) {
    try {
        const response = await fetch(`../Controller/get_item_detail.php?id=${itemId}`);
        const data = await response.json();
        
        if (data.success && data.item) {
            updateItemDetails(data.item);
        } else {
            console.error('Failed to get item details');
        }
    } catch (error) {
        console.error('Error fetching item details:', error);
    }
}

// Update item details in the DOM
function updateItemDetails(item) {
    // Update title
    const titleElement = document.querySelector('h1');
    if (titleElement) titleElement.textContent = item.name;

    // Update rating
    const ratingElement = document.querySelector('.rating-score');
    if (ratingElement) ratingElement.textContent = item.avg_rating || '0';

    // Update location info
    const locationElement = document.querySelector('.location-info p');
    if (locationElement) locationElement.textContent = item.address;

    // Update gallery if exists
    updateGallery(item.images);
}

// Update gallery images
function updateGallery(images) {
    const gallery = document.querySelector('.gallery');
    if (!gallery || !images) return;

    // Clear existing gallery
    gallery.innerHTML = '';

    // Add new images
    images.forEach((img, index) => {
        const div = document.createElement('div');
        div.className = `gallery-item ${index === 0 ? 'parent' : 'child'}`;
        
        const imgElement = document.createElement('img');
        imgElement.src = img.url;
        imgElement.alt = `Gallery Image ${index + 1}`;
        
        div.appendChild(imgElement);
        gallery.appendChild(div);
    });
}

// Initialize IntersectionObserver
function initializeIntersectionObserver(element) {
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                element.classList.add('items-visible');
                observer.unobserve(element);
            }
        });
    }, options);

    observer.observe(element);
}

// Function to load reviews
async function loadReviews() {
    try {
        const itemId = getItemIdFromUrl();
        const response = await fetch(`../Controller/get_reviews.php?id=${itemId}`); // Fixed URL
        const data = await response.json();
        
        if (data.success) {
            displayReviews(data.data);
        } else {
            console.error('Failed to load reviews:', data.message);
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
    }
}

function displayReviews(reviews) {
    const container = document.querySelector('.reviews-container');
    if (!container) {
        console.error('Reviews container not found');
        return;
    }

    if (!reviews || reviews.length === 0) {
        container.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to review!</p>';
        return;
    }

    const reviewsHTML = reviews.map(review => `
        <div class="review-card">
            <div class="review-header">
                <div class="reviewer-info">
                    <img src="${review.profile_image || '../Image/user.png'}" alt="User Profile" class="reviewer-pic">
                    <div class="reviewer-details">
                        <h4>${review.username || 'Anonymous User'}</h4>
                        <span class="review-date">${formatDate(review.created_at)}</span>
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
            <p class="review-text">${review.review_text}</p>
        </div>
    `).join('');

    container.innerHTML = reviewsHTML;
    console.log('Reviews displayed successfully');
}

function generateStars(rating) {
    return Array(5).fill()
        .map((_, index) => `
            <i class='bx ${index < rating ? 'bxs-star' : 'bx-star'}'></i>
        `).join('');
}

function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('en-US', options);
}