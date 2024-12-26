document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');

    if (!itemId || isNaN(parseInt(itemId))) {
        showError('Invalid item ID');
        return;
    }

    showLoading();

    fetch(`../Controller/get_destination_detail.php?id=${itemId}`)
    .then(response => {
        if (!response.ok) throw new Error(`HTTP status ${response.status}`);
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid content type: ' + contentType);
        }
        return response.json();
    })
    .then(data => {
        if (!data || data.error) throw new Error(data.message || 'Invalid data');
        hideLoading();
        updateUI(data);
    })
    .catch(error => {
        console.error('Fetch error:', error, error.stack);
        hideLoading();
        showError(error.message);
    });
});
 
 function showLoading() {
    const main = document.querySelector('.main');
    if (main) main.innerHTML = '<div class="loading">Loading item details...</div>';
 }
 
 function hideLoading() {
    document.querySelector('.loading')?.remove();
 }
 
 function showError(message) {
    const main = document.querySelector('.main');
    if (main) {
        main.innerHTML = `
            <div class="error-message">
                <h2>Oops! Something went wrong</h2>
                <p>${message}</p>
                <button onclick="location.reload()" class="retry-btn">Try Again</button>
            </div>
        `;
    }
 }
 
 function updateUI(data) {
    const item = data.item;
    document.title = `${item.name} - YoXplore`;
    
    const main = document.querySelector('.main');
    if (!main) return;
 
    main.innerHTML = `
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>${item.name}</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">${generateStars(parseFloat(data.rating.average))}</div>
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
            <div class="gallery">${createGalleryHTML(data.images)}</div>
        </div>
    `;
 
    if (item.maps_url) addMap(item.maps_url);
    addReviewsSection(data.reviews || []);
 }
 
 function generateStars(rating) {
    return '<i class="bx bxs-star"></i>'.repeat(Math.floor(rating)) +
           (rating % 1 >= 0.5 ? '<i class="bx bxs-star-half"></i>' : '') +
           '<i class="bx bx-star"></i>'.repeat(5 - Math.ceil(rating));
 }
 
 function formatHours(opening, closing) {
    return opening && closing ? `${opening} - ${closing}` : 'Hours not available';
 }
 
 function createGalleryHTML(images) {
    if (!images?.length) return '<div class="gallery-item parent"><img src="../Image/placeholder.png" alt="No image"></div>';
 
    return images.map((url, i) => `
        <div class="gallery-item ${i === 0 ? 'parent' : 'child'}">
            <img src="${url}" alt="View ${i + 1}" onerror="this.src='../Image/placeholder.png'">
        </div>
    `).join('');
 }
 
 function addMap(url) {
    const container = document.querySelector('.container2');
    if (container) {
        container.insertAdjacentHTML('afterend', `
            <div class="map">
                <iframe src="${url}" width="90%" height="400" style="border:0;border-radius:10px"
                        allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <button class="route-btn" onclick="window.open('${url}', '_blank')">Get Direction</button>
            </div>
        `);
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
                ${reviews.length ? reviews.map(createReviewCard).join('') : 
                '<p class="no-reviews">No reviews yet. Be the first to review!</p>'}
            </div>
        </div>
    `;
 
    const map = document.querySelector('.map') || document.querySelector('.container2');
    map?.insertAdjacentHTML('afterend', reviewsHTML);
 }
 
 function createReviewCard(review) {
    return `
        <div class="review-card">
            <div class="review-header">
                <div class="reviewer-info">
                    <img src="../Image/user.png" alt="User" class="reviewer-pic">
                    <div class="reviewer-details">
                        <h4>${review.username || 'Anonymous'}</h4>
                        <span>${formatDate(review.created_at)}</span>
                    </div>
                </div>
                <div class="review-rating">
                    <div class="star-rating">${generateStars(review.rating)}</div>
                    <span class="rating-score">${review.rating.toFixed(1)}</span>
                    <span class="rating-max">/5</span>
                </div>
            </div>
            <p class="review-text">${review.review_text || ''}</p>
            ${review.images?.length ? createReviewImagesHTML(review.images) : ''}
        </div>
    `;
 }
 
 function createReviewImagesHTML(images) {
    return `
        <div class="review-images">
            ${images.map(url => `
                <img src="${url}" alt="Review image" onerror="this.src='../Image/placeholder.png'">
            `).join('')}
        </div>
    `;
 }
 
 function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
 }