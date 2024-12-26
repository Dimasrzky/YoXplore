document.addEventListener('DOMContentLoaded', function() {
    // Get item ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');

    if (!itemId) {
        showError('No item ID provided');
        return;
    }

    // Show loading state
    showLoading();

    // Fetch item details with error logging
    fetch(`../Controller/get_destination_detail.php?id=${itemId}`)
        .then(response => {
            console.log('Response status:', response.status);
            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            // Log raw response for debugging
            console.log('Raw response:', text);
            
            try {
                // Try to parse as JSON
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response');
            }
        })
        .then(data => {
            console.log('Parsed data:', data);
            
            // Check if data exists and has required structure
            if (!data) {
                throw new Error('No data received');
            }

            // Check if there's an error message in the response
            if (data.error) {
                throw new Error(data.message || 'Server error occurred');
            }

            // Check for required item data
            if (!data.item || !data.item.name) {
                throw new Error('Invalid item data received');
            }

            hideLoading();
            updateUI(data);
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            showError(error.message);
        });
});

function showLoading() {
    const main = document.querySelector('.main');
    if (main) {
        main.innerHTML = `
            <div class="loading" style="text-align: center; padding: 40px;">
                <p>Loading item details...</p>
            </div>
        `;
    }
}

function hideLoading() {
    const loading = document.querySelector('.loading');
    if (loading) {
        loading.remove();
    }
}

function showError(message) {
    const main = document.querySelector('.main');
    if (main) {
        main.innerHTML = `
            <div class="error-message" style="text-align: center; padding: 40px;">
                <h2>Oops! Something went wrong</h2>
                <p>${message}</p>
                <button onclick="location.reload()" style="
                    padding: 10px 20px;
                    margin-top: 20px;
                    background-color: #8B4513;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">
                    Try Again
                </button>
            </div>
        `;
    }
}

function updateUI(data) {
    const item = data.item;
    
    // Update page title
    document.title = `${item.name} - YoXplore`;
    
    // Main content container
    const main = document.querySelector('.main');
    if (!main) return;

    // Build the HTML content
    let contentHTML = `
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>${item.name}</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            ${generateStars(parseFloat(data.rating.average))}
                        </div>
                        <span class="rating-score">${data.rating.average}</span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From ${data.rating.total} users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt="Location"></span>
                                <p>${item.address || 'Address not available'}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt="Hours"></span>
                                <p>${formatHours(item.opening_hours, item.closing_hours)}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt="Phone"></span>
                                <p>${item.phone || 'Not available'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gallery" id="imageGallery">
                ${createGalleryHTML(data.images)}
            </div>
        </div>
    `;

    // Update main content
    main.innerHTML = contentHTML;

    // Add map if available
    if (item.maps_url) {
        addMap(item.maps_url);
    }

    // Add reviews section
    addReviewsSection(data.reviews || []);
}

function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const halfStar = (rating % 1) >= 0.5;
    const emptyStars = 5 - Math.ceil(rating);
    
    return `${'<i class="bx bxs-star"></i>'.repeat(fullStars)}
            ${halfStar ? '<i class="bx bxs-star-half"></i>' : ''}
            ${'<i class="bx bx-star"></i>'.repeat(emptyStars)}`;
}

function formatHours(opening, closing) {
    if (!opening || !closing) return 'Hours not available';
    return `${opening} - ${closing}`;
}

function createGalleryHTML(images) {
    if (!images || !images.length) {
        return '<div class="gallery-item parent"><img src="../Image/placeholder.png" alt="No image available"></div>';
    }

    return images.map((url, index) => `
        <div class="gallery-item ${index === 0 ? 'parent' : 'child'}">
            <img src="${url}" 
                 alt="View image ${index + 1}"
                 onerror="this.src='../Image/placeholder.png'">
        </div>
    `).join('');
}

function addMap(mapsUrl) {
    if (!mapsUrl) return;

    const mapHTML = `
        <div class="map">
            <iframe 
                src="${mapsUrl}"
                width="90%" 
                height="400" 
                style="border:0; border-radius:10px;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            <button class="route-btn" onclick="window.open('${mapsUrl}', '_blank')">
                Get Direction
            </button>
        </div>
    `;

    const container = document.querySelector('.container2');
    if (container) {
        container.insertAdjacentHTML('afterend', mapHTML);
    }
}

function addReviewsSection(reviews) {
    const reviewsHTML = `
        <div class="reviews-section">
            <div class="reviews-header">
                <h2>Users Review</h2>
                <button class="add-review-btn" id="openModal">+ Add Review</button>
            </div>
            <div class="reviews-container">
                ${reviews.length ? 
                    reviews.map(review => createReviewCard(review)).join('') : 
                    '<p class="no-reviews">No reviews yet. Be the first to review!</p>'}
            </div>
        </div>
    `;

    const map = document.querySelector('.map');
    if (map) {
        map.insertAdjacentHTML('afterend', reviewsHTML);
    } else {
        const container = document.querySelector('.container2');
        if (container) {
            container.insertAdjacentHTML('afterend', reviewsHTML);
        }
    }
}

function createReviewCard(review) {
    return `
        <div class="review-card">
            <div class="review-header">
                <div class="reviewer-info">
                    <img src="../Image/user.png" alt="User Profile" class="reviewer-pic">
                    <div class="reviewer-details">
                        <h4>${review.username || 'Anonymous'}</h4>
                        <span>${formatDate(review.created_at)}</span>
                    </div>
                </div>
                <div class="review-rating">
                    <div class="star-rating">
                        ${generateStars(review.rating)}
                    </div>
                    <span class="rating-score">${review.rating.toFixed(1)}</span>
                    <span class="rating-max">/5</span>
                </div>
            </div>
            <p class="review-text">${review.review_text || ''}</p>
        </div>
    `;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}