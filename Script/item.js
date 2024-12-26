function updateUI(data) {
    document.title = `${data.item.name} - YoXplore`;
    
    const contentState = document.getElementById('contentState');
    contentState.style.display = 'block';
    
    contentState.innerHTML = `
        <div class="container2">
            <div class="column">
                <div class="column-right">
                    <h1>${data.item.name}</h1>
                    <div class="review-rating-head">
                        <div class="star-rating">
                            <i class='bx bxs-star'></i>
                        </div>
                        <span class="rating-score">${data.item.rating || '0.0'}</span>
                        <span class="rating-max">/5</span>
                        <span class="rating-user">From ${data.item.total_reviews || 0} users</span>
                    </div>
                    <div class="info-section">
                        <div class="location-info">
                            <div class="info-item">
                                <span class="icon"><img src="../Image/location.png" alt=""></span>
                                <p>${data.item.address || 'Address not available'}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/clock.png" alt=""></span>
                                <p>${data.item.opening_hours || '00:00'} - ${data.item.closing_hours || '00:00'}</p>
                            </div>
                            <div class="info-item">
                                <span class="icon"><img src="../Image/call.png" alt=""></span>
                                <p>${data.item.phone || 'Phone not available'}</p>
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
 
    if (data.item.maps_url) {
        addMap(data.item.maps_url);
    }
 }
 
 function addMap(mapsUrl) {
    const mapHTML = `
        <div class="map">
            <iframe src="${mapsUrl}" width="90%" height="400" 
                style="border:0; border-radius:10px;" 
                allowfullscreen loading="lazy">
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