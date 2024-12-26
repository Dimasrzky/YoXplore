document.addEventListener('DOMContentLoaded', function() {
    loadItemDetails();
    setupModalHandlers();
});

async function loadItemDetails() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const itemId = urlParams.get('id');
        
        if (!itemId) {
            showError('Item ID not found');
            return;
        }

        const response = await fetch(`../Controller/get_item_detail.php?id=${itemId}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to load item details');
        }

        updatePageContent(data);
        
    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

function updatePageContent(data) {
    const { item, images, reviews } = data;

    // Update basic info
    document.querySelector('h1').textContent = item.name;
    document.querySelector('.rating-score').textContent = 
        parseFloat(item.rating || 0).toFixed(1);

    // Update location info
    const infoItems = document.querySelectorAll('.info-item p');
    infoItems[0].textContent = item.address;
    infoItems[1].textContent = `${item.opening_hours} - ${item.closing_hours}`;
    infoItems[2].textContent = item.phone || 'Not Available';

    // Update gallery
    updateGallery(images);
    updateModalGallery(images);

    // Update map
    if (item.maps_url) {
        document.querySelector('.map iframe').src = item.maps_url;
    }

    // Update reviews
    updateReviews(reviews);

    // Update total reviews
    document.querySelector('.rating-user').textContent = 
        `From ${item.total_reviews || 0} users`;
}

function updateGallery(images) {
    const galleryItems = document.querySelectorAll('.gallery .gallery-item:not(.last-item) img');
    images.forEach((image, index) => {
        if (galleryItems[index]) {
            galleryItems[index].src = image.image_url;
            galleryItems[index].alt = `Image ${index + 1}`;
        }
    });

    // Update last item
    const lastItem = document.querySelector('.gallery .last-item img');
    if (lastItem && images.length > 6) {
        lastItem.src = images[6].image_url;
    }
}

function updateModalGallery(images) {
    const modalGallery = document.querySelector('.modal-gallery');
    modalGallery.innerHTML = '';
    
    images.forEach((image, index) => {
        const img = document.createElement('img');
        img.src = image.image_url;
        img.alt = `Photo ${index + 1}`;
        modalGallery.appendChild(img);
    });
}

function updateReviews(reviews) {
    const container = document.querySelector('.reviews-container');
    container.innerHTML = '';

    if (reviews.length === 0) {
        container.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to review!</p>';
        return;
    }

    reviews.forEach(review => {
        const reviewCard = createReviewCard(review);
        container.appendChild(reviewCard);
    });
}

function createReviewCard(review) {
    const card = document.createElement('div');
    card.className = 'review-card';
    
    const date = new Date(review.created_at).toLocaleDateString();
    
    card.innerHTML = `
        <div class="review-header">
            <div class="reviewer-info">
                <img src="${review.profile_image || '../Image/user.png'}" alt="User Profile" class="reviewer-pic">
                <div class="reviewer-details">
                    <h4>${review.username}</h4>
                    <span class="review-date">${date}</span>
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
        <p class="review-text">${review.comment}</p>
        ${review.images.length > 0 ? `
            <div class="review-images">
                ${review.images.map(img => `<img src="${img}" alt="Review Image">`).join('')}
            </div>
        ` : ''}
    `;
    
    return card;
}

function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += `<i class='bx ${i <= rating ? 'bxs-star' : 'bx-star'}'></i>`;
    }
    return stars;
}

function setupModalHandlers() {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const stars = document.querySelectorAll('.rating .star');
    
    openModalBtn.onclick = () => modal.style.display = 'flex';
    closeModalBtn.onclick = () => modal.style.display = 'none';
    
    stars.forEach((star, index) => {
        star.onclick = () => {
            stars.forEach((s, i) => {
                s.classList.toggle('bxs-star', i <= index);
                s.classList.toggle('bx-star', i > index);
            });
            document.querySelector('input[name="rating"]').value = index + 1;
        };
    });

    // Handle image preview
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('image-preview');
    
    fileInput.onchange = () => {
        previewContainer.innerHTML = '';
        [...fileInput.files].forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    };
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    document.querySelector('.main').prepend(errorDiv);
    
    setTimeout(() => errorDiv.remove(), 5000);
}