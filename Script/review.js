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
        const itemId = new URLSearchParams(window.location.search).get('id');
        console.log('Loading reviews for item:', itemId);

        const response = await fetch(`../Controller/get_reviews.php?id=${itemId}`);
        const data = await response.json();
        console.log('Review data received:', data); // Debug data yang diterima
        
        // Pastikan data.data ada dan merupakan array
        if (data.success && Array.isArray(data.data)) {
            displayReviews(data.data);
        } else {
            console.log('No reviews found or invalid data structure:', data);
            displayReviews([]); // Pass empty array jika tidak ada review
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
        const container = document.querySelector('.reviews-container');
        if (container) {
            container.innerHTML = '<p class="error">Error loading reviews. Please try again later.</p>';
        }
    }
}

// Function to display reviews
function displayReviews(reviews) {
    const container = document.querySelector('.reviews-container');
    if (!container) {
        console.error('Reviews container not found');
        return;
    }

    // Check if reviews is array and has items
    if (!Array.isArray(reviews) || reviews.length === 0) {
        container.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to review!</p>';
        return;
    }

    const reviewsHTML = reviews.map(review => {
        // Pastikan review memiliki semua properti yang dibutuhkan
        const username = review.username || 'Anonymous User';
        const rating = review.rating || 0;
        const reviewText = review.review_text || 'No comment';
        const profileImage = review.profile_image || '../Image/user.png';
        const createdAt = review.created_at ? formatDate(review.created_at) : 'Date not available';

        return `
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <img src="${profileImage}" alt="User Profile" class="reviewer-pic" onerror="this.src='../Image/user.png'">
                        <div class="reviewer-details">
                            <h4>${username}</h4>
                            <span class="review-date">${createdAt}</span>
                        </div>
                    </div>
                    <div class="review-rating">
                        <div class="star-rating">
                            ${generateStars(rating)}
                        </div>
                        <span class="rating-score">${rating}</span>
                        <span class="rating-max">/5</span>
                    </div>
                </div>
                <p class="review-text">${reviewText}</p>
            </div>
        `;
    }).join('');

    container.innerHTML = reviewsHTML;
    console.log('Reviews displayed successfully');
}

// Helper function untuk generate stars
function generateStars(rating) {
    rating = Number(rating) || 0; // Pastikan rating adalah number
    return Array(5).fill()
        .map((_, index) => `
            <i class='bx ${index < rating ? 'bxs-star' : 'bx-star'}'></i>
        `).join('');
}

// Helper function untuk format tanggal
function formatDate(dateString) {
    try {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Date(dateString).toLocaleDateString('en-US', options);
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString; // Return original string if formatting fails
    }
}

// Call loadReviews when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document loaded, initializing reviews...');
    loadReviews();
});