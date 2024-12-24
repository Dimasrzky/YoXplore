document.addEventListener('DOMContentLoaded', function() {
    // Get item ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');

    if (!itemId) {
        showError('Item ID not found');
        return;
    }

    // Fetch item details
    fetchItemDetails(itemId);
});

async function fetchItemDetails(itemId) {
    try {
        const response = await fetch(`../Controller/get_item_detail.php?id=${itemId}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        updateUI(data);
    } catch (error) {
        showError('Failed to load item details: ' + error.message);
    }
}

function updateUI(data) {
    const { item, images, reviews } = data;

    // Update header
    document.querySelector('.item-title').textContent = item.item_name;
    document.querySelector('.rating').textContent = item.rating.toFixed(1);

    // Update info
    document.querySelector('.info-item:nth-child(1) p').textContent = item.address;
    document.querySelector('.info-item:nth-child(2) p').textContent = item.opening_hours;
    document.querySelector('.info-item:nth-child(3) p').textContent = item.phone;

    // Update gallery
    updateGallery(images);

    // Update reviews
    updateReviews(reviews);
}

function updateGallery(images) {
    const mainImage = images.find(img => img.is_main) || images[0];
    const galleryContainer = document.querySelector('.gallery-container');
    
    // Set main image
    document.querySelector('.main-image img').src = mainImage.image_url;

    // Update thumbnails
    const thumbnailsContainer = document.querySelector('.thumbnail-grid');
    thumbnailsContainer.innerHTML = '';

    images.slice(1, 5).forEach((image, index) => {
        const thumbnail = document.createElement('div');
        thumbnail.className = `thumbnail ${index === 3 ? 'last-thumbnail' : ''}`;
        
        const img = document.createElement('img');
        img.src = image.image_url;
        img.alt = `Gallery image ${index + 2}`;
        
        thumbnail.appendChild(img);

        if (index === 3) {
            const overlay = document.createElement('div');
            overlay.className = 'see-all-overlay';
            overlay.innerHTML = '<span>See All Photos</span>';
            overlay.onclick = () => openPhotoModal(images);
            thumbnail.appendChild(overlay);
        }

        thumbnailsContainer.appendChild(thumbnail);
    });
}

function updateReviews(reviews) {
    const reviewsContainer = document.querySelector('.reviews-container');
    reviewsContainer.innerHTML = '';

    reviews.forEach(review => {
        const reviewCard = createReviewCard(review);
        reviewsContainer.appendChild(reviewCard);
    });
}

function createReviewCard(review) {
    const card = document.createElement('div');
    card.className = 'review-card';
    
    card.innerHTML = `
        <div class="review-header">
            <div class="reviewer-info">
                <div class="profile-pic">
                    <img src="${review.profile_image || '../Image/user.png'}" alt="User Profile">
                </div>
                <div class="reviewer-name">
                    <h4>${review.username}</h4>
                </div>
            </div>
            <div class="review-rating">
                <div class="stars">
                    <i class='bx bxs-star'></i>
                </div>
                <span class="rating">${review.rating.toFixed(1)}</span>
                <span class="rating-max">/5</span>
            </div>
        </div>
        <p class="review-text">${review.review_text}</p>
        ${review.images.length > 0 ? `
            <div class="review-images">
                ${review.images.map(img => `
                    <img src="${img.image_url}" alt="Review image">
                `).join('')}
            </div>
        ` : ''}
    `;

    return card;
}

function openPhotoModal(images) {
    const modal = document.getElementById('photoModal');
    const gallery = modal.querySelector('.modal-gallery');
    
    gallery.innerHTML = images.map(img => `
        <img src="${img.image_url}" alt="Gallery photo">
    `).join('');

    modal.style.display = 'block';
}

function showError(message) {
    // Add error handling UI as needed
    console.error(message);
}