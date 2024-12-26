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

    // Fetch item details
    fetch(`../Controller/get_destination_detail.php?id=${itemId}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.text().then(text => {
                try {
                    // Try to parse as JSON
                    console.log('Raw response:', text);
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.log('Response text:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            console.log('Parsed data:', data);
            
            if (!data || !data.item) {
                throw new Error('Invalid data structure');
            }

            hideLoading();
            updateUI(data);
        })
        .catch(error => {
            console.error('Error:', error);
            showError(error.message);
        });
});

// UI Helper Functions
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

    main.innerHTML = `
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>${item.name}</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <i class='bx bxs-star'></i>
                        </div>
                        <span class="rating-score">${data.rating.average}</span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From ${data.rating.total} users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p>${item.address || 'Address not available'}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p>${item.opening_hours || '00:00'} - ${item.closing_hours || '00:00'}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
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

    // Add map if available
    if (item.maps_url) {
        addMap(item.maps_url);
    }

    // Add reviews section
    addReviewsSection(data.reviews);
}

function createGalleryHTML(images) {
    if (!images || !images.length) {
        return '<div class="gallery-item parent"><img src="../Image/placeholder.png" alt="No image available"></div>';
    }

    return images.map((url, index) => `
        <div class="gallery-item ${index === 0 ? 'parent' : 'child'}">
            <img src="${url}" alt="Item image ${index + 1}" onerror="this.src='../Image/placeholder.png'">
        </div>
    `).join('');
}

function addMap(mapsUrl) {
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
    document.querySelector('.container2').insertAdjacentHTML('afterend', mapHTML);
}

function addReviewsSection(reviews) {
    const reviewsHTML = `
        <div class="reviews-section">
            <div class="reviews-header">
                <h2>Users Review</h2>
                <button class="add-review-btn" id="openModal">+ Add Review</button>
            </div>
            <div class="reviews-container">
                ${reviews.length ? createReviewsHTML(reviews) : '<p class="no-reviews">No reviews yet. Be the first to review!</p>'}
            </div>
        </div>
    `;
    document.querySelector('.map').insertAdjacentHTML('afterend', reviewsHTML);
}