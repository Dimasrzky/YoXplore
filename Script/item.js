// Function to load item details
function loadItemDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');
    
    if (!itemId) {
        showError('Item ID not found');
        return;
    }

    fetch(`../Controller/get_item_detail.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePageContent(data);
            } else {
                showError(data.message || 'Failed to load item details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load item details');
        });
}

// Function to update page content
function updatePageContent(data) {
    const { item, images, reviews } = data;
    
    // Update basic information
    document.querySelector('h1').textContent = item.name;
    document.querySelector('.rating-score').textContent = item.rating.toFixed(1);
    
    // Update location info
    const locationInfo = document.querySelector('.location-info');
    locationInfo.querySelector('p').textContent = item.address;
    locationInfo.querySelectorAll('p')[1].textContent = item.opening_hours;
    locationInfo.querySelectorAll('p')[2].textContent = item.phone;
    
    // Update gallery
    updateGallery(images);
    
    // Update reviews
    updateReviews(reviews);
    
    // Update map
    updateMap(item);
}

// Function to update gallery
function updateGallery(images) {
    const gallery = document.querySelector('.gallery');
    gallery.innerHTML = ''; // Clear existing images
    
    images.forEach((image, index) => {
        const galleryItem = document.createElement('div');
        galleryItem.className = `gallery-item ${index === 0 ? 'parent' : 'child'}`;
        
        const img = document.createElement('img');
        img.src = image.image_url;
        img.alt = `Gallery Image ${index + 1}`;
        
        galleryItem.appendChild(img);
        gallery.appendChild(galleryItem);
    });
}

// Function to update reviews
function updateReviews(reviews) {
    const reviewsContainer = document.querySelector('.reviews-container');
    reviewsContainer.innerHTML = ''; // Clear existing reviews
    
    reviews.forEach(review => {
        const reviewCard = document.createElement('div');
        reviewCard.className = 'review-card';
        
        reviewCard.innerHTML = `
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
            <p class="review-text">${review.comment}</p>
            ${review.images.length > 0 ? `
                <div class="review-images">
                    ${review.images.map(img => `<img src="${img}" alt="Review Image">`).join('')}
                </div>
            ` : ''}
        `;
        
        reviewsContainer.appendChild(reviewCard);
    });
}

// Function to update map
function updateMap(item) {
    const mapIframe = document.querySelector('.map iframe');
    const mapUrl = `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.115726227853!2d${item.longitude}!3d${item.latitude}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0:0x0!2zM!5e0!3m2!1sen!2sid!4v1624442830000!5m2!1sen!2sid`;
    mapIframe.src = mapUrl;
}

// Function to handle add review
function handleAddReview() {
    const modal = document.getElementById('modal');
    const stars = document.querySelectorAll('.star');
    const submitBtn = modal.querySelector('.submit');
    let rating = 0;

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            rating = index + 1;
            stars.forEach((s, i) => {
                s.classList.toggle('bxs-star', i <= index);
                s.classList.toggle('bx-star', i > index);
            });
        });
    });

    submitBtn.addEventListener('click', () => {
        const comment = modal.querySelector('textarea').value;
        const fileInput = modal.querySelector('#file-input');
        const formData = new FormData();
        
        formData.append('rating', rating);
        formData.append('comment', comment);
        
        if (fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach(file => {
                formData.append('images[]', file);
            });
        }
        
        submitReview(formData);
    });
}

// Function to submit review
async function submitReview(formData) {
    try {
        const response = await fetch('../Controller/add_review.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal();
            loadItemDetails(); // Reload the page content
        } else {
            showError(data.message || 'Failed to submit review');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Failed to submit review');
    }
}

// Function to show error messages
function showError(message) {
    // You can customize how errors are displayed
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    // Insert error message at the top of the main content
    const mainContent = document.querySelector('.main');
    mainContent.insertBefore(errorDiv, mainContent.firstChild);
    
    // Optional: Remove error message after few seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    loadItemDetails();
    handleAddReview();
});