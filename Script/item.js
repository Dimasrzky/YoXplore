document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');
 
    if (!itemId || isNaN(parseInt(itemId))) {
        showError('Invalid item ID');
        return;
    }
 
    showLoading();
 
    const url = `../Controller/get_destination_detail.php?id=${itemId}`;
    console.log('Fetching from:', url);
 
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers));
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Invalid JSON response');
        }
    })
    .then(data => {
        console.log('Parsed data:', data);
        
        if (!data) {
            throw new Error('No data received');
        }
        if (data.error) {
            throw new Error(data.message || 'Server error occurred');
        }
        if (!data.item || !data.item.name) {
            throw new Error('Invalid item data received');
        }
 
        hideLoading();
        updateUI(data);
    })
    .catch(error => {
        console.error('Full error details:', {
            message: error.message,
            stack: error.stack,
            cause: error.cause
        });
        hideLoading();
        showError(`Error: ${error.message}`);
    });
 });
 
 function showLoading() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('errorState').style.display = 'none';
    document.getElementById('contentState').style.display = 'none';
 }
 
 function hideLoading() {
    document.getElementById('loadingState').style.display = 'none';
 }
 
 function showError(message) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('contentState').style.display = 'none';
    
    const errorState = document.getElementById('errorState');
    errorState.style.display = 'block';
    errorState.innerHTML = `
        <div class="error-message">
            <h2>Oops! Something went wrong</h2>
            <p>${message}</p>
            <button onclick="location.reload()" class="retry-btn">Try Again</button>
        </div>
    `;
 }
 
 // item.js
function updateUI(data) {
    document.title = `${data.items.name} - YoXplore`;
    
    const contentHTML = `
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>${data.items.name}</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <i class='bx bxs-star'></i>
                        </div>
                        <span class="rating-score">${data.items.rating}</span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From ${data.items.total_reviews} users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p>${data.items.address}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p>${data.items.opening_hours} - ${data.items.closing_hours}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p>${data.items.phone}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gallery">
                ${data.images.map((url, i) => `
                    <div class="gallery-item ${i === 0 ? 'parent' : 'child'}">
                        <img src="${url}" alt="Image ${i+1}" onerror="this.src='../Image/placeholder.jpg'">
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    document.querySelector('.main').innerHTML = contentHTML;

    if (data.item.maps_url) {
        addMap(data.item.maps_url);
    }
}
 
 // Include remaining helper functions (generateStars, formatHours, createGalleryHTML, addMap, addReviewsSection, etc.)
 // as they were in the previous code