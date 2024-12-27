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
    if (!container) return;

    if (!reviews.length) {
        container.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to review!</p>';
        return;
    }

    const reviewsHTML = reviews.map(review => {
        // Generate images HTML if exists
        const imagesHTML = review.images && review.images.length ? `
            <div class="review-images">
                ${review.images.map(img => `
                    <img src="../${img}" alt="Review image" 
                         onclick="openImageModal('${img}')"
                         onerror="this.style.display='none'">
                `).join('')}
            </div>
        ` : '';

        return `
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <img src="${review.profile_image || '../Image/user.png'}" 
                             alt="Profile" 
                             class="reviewer-pic"
                             onerror="this.src='../Image/user.png'">
                        <div class="reviewer-details">
                            <h4>${review.username || 'Anonymous'}</h4>
                            <span class="review-date">${formatDate(review.created_at)}</span>
                        </div>
                    </div>
                    <div class="review-rating">
                        <div class="star-rating">
                            ${generateStars(review.rating)}
                        </div>
                        <span class="rating-score">${review.rating}/5</span>
                    </div>
                </div>
                <p class="review-text">${review.review_text}</p>
                ${imagesHTML}
            </div>
        `;
    }).join('');

    container.innerHTML = reviewsHTML;
}

// Add CSS for review images
const style = document.createElement('style');
style.textContent = `
    .review-images {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    
    .review-images img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
    }
`;
document.head.appendChild(style);

let isSubmitting = false; // Flag untuk mencegah double submission

document.querySelector('.submit').addEventListener('click', async function(e) {
    e.preventDefault();
    
    // Cek jika sedang proses submit
    if (isSubmitting) {
        console.log('Submission in progress, please wait...');
        return;
    }

    try {
        isSubmitting = true; // Set flag
        const rating = document.querySelector('[name="rating"]').value;
        const comment = document.querySelector('textarea').value;
        const itemId = new URLSearchParams(window.location.search).get('id');
        
        // Validasi input
        if (!rating) {
            alert('Please select a rating');
            return;
        }
        
        if (!comment.trim()) {
            alert('Please write a review');
            return;
        }

        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('rating', rating);
        formData.append('comment', comment);
        
        // Handle file uploads
        const fileInput = document.getElementById('file-input');
        if (fileInput && fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach(file => {
                formData.append('images[]', file);
            });
        }

        const response = await fetch('../Controller/save_review.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Review submitted successfully!');
            modal.classList.remove('open');
            resetForm();
            loadReviews(); // Reload reviews
        } else {
            throw new Error(data.message || 'Failed to submit review');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Error submitting review');
    } finally {
        isSubmitting = false; // Reset flag
    }
});

// Fungsi untuk reset form
function resetForm() {
    document.querySelector('textarea').value = '';
    document.querySelector('[name="rating"]').value = '';
    const fileInput = document.getElementById('file-input');
    if (fileInput) fileInput.value = '';
    
    // Reset star rating
    const stars = document.querySelectorAll('.rating .star');
    stars.forEach(star => {
        star.classList.replace('bxs-star', 'bx-star');
        star.classList.remove('active');
    });
    
    // Clear preview images
    const previewContainer = document.getElementById('image-preview');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
}