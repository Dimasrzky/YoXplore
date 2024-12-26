async function loadItemDetails() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const itemId = urlParams.get('id');
        
        if (!itemId) {
            throw new Error('Item ID not found');
        }

        const response = await fetch(`../Controller/get_item_detail.php?id=${itemId}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const jsonData = await response.json();
        console.log('Response:', jsonData); // Debug log
        
        if (!jsonData.success) {
            throw new Error(jsonData.message || 'Failed to load item details');
        }

        updatePageContent(jsonData.data);
        
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

function updatePageContent(data) {
    const { item, images } = data;

    try {
        // Update basic info
        document.querySelector('h1').textContent = item.name || '';
        document.querySelector('.rating-score').textContent = 
            parseFloat(item.rating || 0).toFixed(1);

        // Update location info
        const infoItems = document.querySelectorAll('.info-item p');
        infoItems[0].textContent = item.address || '';
        infoItems[1].textContent = `${item.opening_hours || ''} - ${item.closing_hours || ''}`;
        infoItems[2].textContent = item.phone || '-';

        // Update gallery if images exist
        if (images && images.length > 0) {
            updateGallery(images);
            updateModalGallery(images);
        }

        // Update reviews section
        updateReviews(data.reviews || []);

        // Update total reviews
        document.querySelector('.rating-user').textContent = 
            `From ${item.total_reviews || 0} users`;

    } catch (error) {
        console.error('Error updating content:', error);
        showError('Error updating page content');
    }
}

function updateGallery(images) {
    const gallery = document.querySelector('.gallery');
    const galleryItems = gallery.querySelectorAll('.gallery-item:not(.last-item) img');
    
    images.forEach((image, index) => {
        if (galleryItems[index]) {
            galleryItems[index].src = image.image_url || '';
            galleryItems[index].alt = `Image ${index + 1}`;
        }
    });

    // Update last item
    const lastItem = gallery.querySelector('.last-item img');
    if (lastItem && images.length > 6) {
        lastItem.src = images[6].image_url || '';
    }
}

function updateModalGallery(images) {
    const modalGallery = document.querySelector('.modal-gallery');
    if (!modalGallery) return;
    
    modalGallery.innerHTML = '';
    
    images.forEach((image, index) => {
        if (image.image_url) {
            const img = document.createElement('img');
            img.src = image.image_url;
            img.alt = `Photo ${index + 1}`;
            modalGallery.appendChild(img);
        }
    });
}

function updateReviews(reviews) {
    const container = document.querySelector('.reviews-container');
    if (!container) return;

    container.innerHTML = reviews.length === 0 
        ? '<p class="no-reviews">No reviews yet. Be the first to review!</p>'
        : reviews.map(review => createReviewCard(review)).join('');
}

function createReviewCard(review) {
    return `
        <div class="review-card">
            <div class="review-header">
                <div class="reviewer-info">
                    <img src="${review.profile_image || '../Image/user.png'}" alt="User Profile" class="reviewer-pic">
                    <div class="reviewer-details">
                        <h4>${review.username || 'Anonymous'}</h4>
                    </div>
                </div>
                <div class="review-rating">
                    <div class="star-rating">
                        <i class='bx bxs-star'></i>
                    </div>
                    <span class="rating-score">${review.rating || 0}</span>
                    <span class="rating-max">/5</span>
                </div>
            </div>
            <p class="review-text">${review.comment || ''}</p>
            ${review.images ? `
                <div class="review-images">
                    ${review.images.map(img => `
                        <img src="${img}" alt="Review Image">
                    `).join('')}
                </div>
            ` : ''}
        </div>
    `;
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    const main = document.querySelector('.main');
    main.insertBefore(errorDiv, main.firstChild);
    
    setTimeout(() => errorDiv.remove(), 5000);
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    loadItemDetails();
});