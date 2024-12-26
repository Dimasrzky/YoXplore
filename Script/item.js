document.addEventListener('DOMContentLoaded', function() {
    // Get item ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');

    if (!itemId) {
        window.location.href = 'Home.html';
        return;
    }

    // Fetch item details
    fetch(`../Controller/get_item_detail.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            const item = data.item;
            
            // Update page title
            document.title = `${item.name} - YoXplore`;
            
            // Update basic information
            document.getElementById('itemName').textContent = item.name;
            document.getElementById('ratingScore').textContent = data.rating.average;
            document.getElementById('totalReviews').textContent = `From ${data.rating.total} users`;
            document.getElementById('itemAddress').textContent = item.address;
            document.getElementById('itemHours').textContent = `${item.opening_hours} - ${item.closing_hours}`;
            document.getElementById('itemPhone').textContent = item.phone || 'Not available';

            // Update map
            if (item.maps_url) {
                document.getElementById('mapFrame').src = item.maps_url;
                document.getElementById('directionBtn').onclick = () => {
                    window.open(item.maps_url, '_blank');
                };
            }

            // Update gallery images
            const gallery = document.getElementById('imageGallery');
            data.images.forEach((imageUrl, index) => {
                const galleryItem = document.createElement('div');
                galleryItem.className = `gallery-item ${index === 0 ? 'parent' : 'child'}`;
                
                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = `${item.name} Image ${index + 1}`;
                
                galleryItem.appendChild(img);
                gallery.appendChild(galleryItem);
            });

            // Update reviews
            const reviewsContainer = document.getElementById('reviewsContainer');
            data.reviews.forEach(review => {
                const reviewCard = createReviewCard(review);
                reviewsContainer.appendChild(reviewCard);
            });
        })
        .catch(error => {
            console.error('Error fetching item details:', error);
            alert('Failed to load item details. Please try again later.');
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
                    <h4>${review.username}</h4>
                    <span>${new Date(review.created_at).toLocaleDateString()}</span>
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
        ${review.image_urls ? createReviewImages(review.image_urls) : ''}
    `;
    
    return card;
}

// Function to generate star rating
function generateStars(rating) {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
}

// Function to create review images
function createReviewImages(imageUrls) {
    if (!imageUrls || !imageUrls.length) return '';
    
    const images = imageUrls.map(url => `
        <img src="${url}" alt="Review Image">
    `).join('');
    
    return `<div class="review-images">${images}</div>`;
}

// Modal handling
const modal = document.getElementById('modal');
const openModalBtn = document.getElementById('openModal');
const closeModalBtn = document.getElementById('closeModal');
const stars = document.querySelectorAll('.modal .star');
const ratingInput = document.querySelector('.modal input[name="rating"]');

openModalBtn.addEventListener('click', () => {
    modal.style.display = 'block';
});

closeModalBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

// Star rating handling
stars.forEach((star, index) => {
    star.addEventListener('click', () => {
        ratingInput.value = index + 1;
        stars.forEach((s, i) => {
            s.classList.toggle('active', i <= index);
        });
    });
});

// Handle form submission
document.getElementById('reviewForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const urlParams = new URLSearchParams(window.location.search);
    formData.append('item_id', urlParams.get('id'));
    
    try {
        const response = await fetch('../Controller/add_review.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
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