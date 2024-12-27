// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeReviewSystem();
});

// Initialize the review system
function initializeReviewSystem() {
    // Initialize components
    initializeStarRating();
    initializeModalHandlers();
    initializeSubmitHandler();
    loadReviews(); // Load existing reviews
}

// Star Rating Handler
function initializeStarRating() {
    const stars = document.querySelectorAll('.rating .star');
    const ratingInput = document.querySelector('[name="rating"]');

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;
            ratingInput.value = rating;
            updateStars(index);
        });

        star.addEventListener('mouseover', () => {
            updateStars(index);
        });

        star.addEventListener('mouseleave', () => {
            updateStars(ratingInput.value - 1);
        });
    });
}

// Update star display
function updateStars(activeIndex) {
    const stars = document.querySelectorAll('.rating .star');
    stars.forEach((star, index) => {
        if (index <= activeIndex) {
            star.classList.replace('bx-star', 'bxs-star');
            star.classList.add('active');
        } else {
            star.classList.replace('bxs-star', 'bx-star');
            star.classList.remove('active');
        }
    });
}

// Modal Handlers
function initializeModalHandlers() {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');

    openModalBtn.addEventListener('click', () => {
        modal.classList.add('open');
    });

    closeModalBtn.addEventListener('click', () => {
        modal.classList.remove('open');
        resetForm();
    });

    // Close on outside click
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('open');
            resetForm();
        }
    });
}

// Submit Handler
function initializeSubmitHandler() {
    const submitBtn = document.querySelector('.submit');
    let isSubmitting = false;

    submitBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        if (isSubmitting) return;
        isSubmitting = true;
        submitBtn.disabled = true;

        try {
            const rating = document.querySelector('[name="rating"]').value;
            const comment = document.querySelector('textarea').value;
            const itemId = new URLSearchParams(window.location.search).get('id');

            // Validate inputs
            if (!rating) {
                throw new Error('Please select a rating');
            }
            
            if (!comment.trim()) {
                throw new Error('Please write a review');
            }

            const formData = new FormData();
            formData.append('item_id', itemId);
            formData.append('rating', rating);
            formData.append('comment', comment);

            const response = await fetch('../Controller/save_review.php', {
                method: 'POST',
                body: formData
            });

            const responseText = await response.text();
            console.log('Raw response:', responseText);

            const data = JSON.parse(responseText);

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
            isSubmitting = false;
            submitBtn.disabled = false;
        }
    });
}

async function loadReviews() {
    try {
        const itemId = new URLSearchParams(window.location.search).get('id');
        console.log('Loading reviews for item:', itemId);

        if (!itemId) {
            throw new Error('No item ID found');
        }

        const response = await fetch(`../Controller/get_reviews.php?id=${itemId}`);
        const responseText = await response.text();

        // Try to parse the response
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Invalid JSON response:', responseText);
            throw new Error('Invalid server response');
        }

        if (!data) {
            throw new Error('Empty response from server');
        }

        console.log('Review data:', data);

        if (data.success && Array.isArray(data.data)) {
            displayReviews(data.data);
        } else {
            displayReviews([]); // Show empty state
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
        const container = document.querySelector('.reviews-container');
        if (container) {
            container.innerHTML = `<p class="error">Error loading reviews: ${error.message}</p>`;
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
        // Sanitize data
        const username = (review.username || 'Anonymous User').replace(/[<>]/g, '');
        const rating = parseInt(review.rating) || 0;
        const reviewText = (review.review_text || 'No comment').replace(/[<>]/g, '');
        const profileImage = review.profile_image || '../Image/user.png';

        return `
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <img src="${profileImage}" 
                             alt="Profile" 
                             class="reviewer-pic" 
                             onerror="this.src='../Image/user.png'">
                        <div class="reviewer-details">
                            <h4>${username}</h4>
                            <span class="review-date">${formatDate(review.created_at)}</span>
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