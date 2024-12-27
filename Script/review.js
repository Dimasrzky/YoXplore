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

async function loadReviews() {
    try {
        const itemId = new URLSearchParams(window.location.search).get('id');
        console.log('Loading reviews for item:', itemId);

        const response = await fetch(`../Controller/get_reviews.php?id=${itemId}`);
        const responseText = await response.text();
        
        console.log('Raw response:', responseText); // Debug log

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Invalid server response');
        }

        console.log('Parsed data:', data);

        if (data.success && Array.isArray(data.data)) {
            displayReviews(data.data);
        } else {
            console.log('No reviews or invalid data structure');
            displayReviews([]);
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
        const container = document.querySelector('.reviews-container');
        if (container) {
            container.innerHTML = `
                <div class="error-message">
                    <p>Error loading reviews. Please try again later.</p>
                    <small>${error.message}</small>
                </div>
            `;
        }
    }
}

function displayReviews(reviews) {
    const container = document.querySelector('.reviews-container');
    
    if (!container) {
        console.error('Reviews container not found');
        return;
    }

    if (!Array.isArray(reviews) || reviews.length === 0) {
        container.innerHTML = `
            <div class="no-reviews">
                <p>No reviews yet. Be the first to review!</p>
            </div>
        `;
        return;
    }

    const reviewsHTML = reviews.map(review => {
        // Sanitize data to prevent XSS
        const username = (review.username || 'Anonymous').replace(/[<>]/g, '');
        const rating = parseInt(review.rating) || 0;
        const reviewText = (review.review_text || '').replace(/[<>]/g, '');
        
        return `
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <img src="${review.profile_image || '../Image/user.png'}" 
                             alt="Profile" 
                             class="reviewer-pic"
                             onerror="this.src='../Image/user.png'">
                        <div class="reviewer-details">
                            <h4>${username}</h4>
                            <span class="review-date">
                                ${formatDate(review.created_at)}
                            </span>
                        </div>
                    </div>
                    <div class="review-rating">
                        <div class="star-rating">
                            ${generateStars(rating)}
                        </div>
                        <span class="rating-score">${rating}/5</span>
                    </div>
                </div>
                <p class="review-text">${reviewText}</p>
            </div>
        `;
    }).join('');

    container.innerHTML = reviewsHTML;
}

function generateStars(rating) {
    return Array(5).fill()
        .map((_, i) => `
            <i class='bx ${i < rating ? 'bxs-star' : 'bx-star'}'></i>
        `).join('');
}

function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch (e) {
        console.error('Date format error:', e);
        return dateString;
    }
}