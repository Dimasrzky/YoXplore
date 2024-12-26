document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');
 
    if (!itemId) {
        showError('Invalid item ID');
        return;
    }
 
    showLoading();
 
    fetch(`../Controller/get_destination_detail.php?id=${itemId}`, {
        headers: {'Accept': 'application/json'}
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.text(); 
    })
    .then(text => {
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Raw response:', text);
            throw new Error('Invalid JSON response');
        }
    })
    .then(data => {
        hideLoading();
        if (!data || !data.item) throw new Error('Invalid data structure');
        updateUI(data);
    })
    .catch(error => {
        hideLoading();
        showError(error.message);
    });
 });
 
 function updateUI(data) {
    const item = data.item;
    
    document.querySelector('#contentState').innerHTML = `
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>${item.name}</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            ${generateStars(item.rating || 0)}
                        </div>
                        <span class="rating-score">${item.rating || '0'}</span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From ${item.total_reviews || 0} users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p>${item.address || 'Address not available'}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p>${formatHours(item.opening_hours, item.closing_hours)}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p>${item.phone || 'Phone not available'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gallery">
                ${createGalleryHTML(data.images)}
            </div>
        </div>
    `;
 
    document.getElementById('contentState').style.display = 'block';
    
    if (item.maps_url) {
        addMap(item.maps_url);
    }
 }
 
 function generateStars(rating) {
    return '★'.repeat(Math.floor(rating)) + 
           '☆'.repeat(5 - Math.floor(rating));
 }
 
 function formatHours(opening, closing) {
    return opening && closing ? 
        `${opening} - ${closing}` : 
        'Hours not available';
 }
 
 function createGalleryHTML(images) {
    if (!images?.length) {
        return '<img src="../Image/placeholder.jpg" alt="No image available">';
    }
    
    return images.map((url, i) => `
        <div class="gallery-item ${i === 0 ? 'parent' : 'child'}">
            <img src="${url}" alt="View ${i + 1}" 
                 onerror="this.src='../Image/placeholder.jpg'">
        </div>
    `).join('');
 }
 
 function addMap(url) {
    const mapHTML = `
        <div class="map">
            <iframe src="${url}" width="90%" height="400" 
                    style="border:0; border-radius:10px;" 
                    allowfullscreen loading="lazy">
            </iframe>
            <button class="route-btn" onclick="window.open('${url}', '_blank')">
                Get Direction
            </button>
        </div>
    `;
    document.querySelector('.container2').insertAdjacentHTML('afterend', mapHTML);
 }