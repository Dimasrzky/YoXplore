document.addEventListener('DOMContentLoaded', function() {
    const itemId = new URLSearchParams(window.location.search).get('id');
    
    if (!itemId) {
        showError('No item ID provided');
        return;
    }
 
    document.querySelector('.loading').style.display = 'block';
 
    fetch(`../Controller/get_destination_detail.php?id=${itemId}`, {
        headers: {'Accept': 'application/json'}
    })
    .then(response => response.json())
    .then(data => {
        document.querySelector('.loading').style.display = 'none';
        if (!data.item) throw new Error('Invalid data received');
        updateUI(data);
    })
    .catch(error => {
        document.querySelector('.loading').style.display = 'none';
        document.querySelector('.main').innerHTML = `
            <div class="error">
                <h2>Error loading details</h2>
                <p>${error.message}</p>
                <button onclick="location.reload()">Retry</button>
            </div>
        `;
    });
 });
 
 function updateUI(data) {
    const item = data.item;
    document.querySelector('.main').innerHTML = `
        <div class="container2">
            <div class="column-right">
                <h1>${item.name}</h1>
                <div class="info-section">
                    <div class="info-item">
                        <span class="icon"><img src="../Image/location.png"></span>
                        <p>${item.address || 'Not available'}</p>
                    </div>
                    <div class="info-item">
                        <span class="icon"><img src="../Image/clock.png"></span>
                        <p>${item.opening_hours || '00:00'} - ${item.closing_hours || '00:00'}</p>
                    </div>
                    <div class="info-item">
                        <span class="icon"><img src="../Image/call.png"></span>
                        <p>${item.phone || 'Not available'}</p>
                    </div>
                </div>
            </div>
            <div class="gallery">
                ${data.images.map((url, i) => `
                    <div class="gallery-item ${i === 0 ? 'parent' : 'child'}">
                        <img src="${url}" onerror="this.src='../Image/placeholder.jpg'">
                    </div>
                `).join('')}
            </div>
        </div>
    `;
 
    if (item.maps_url) {
        document.querySelector('.container2').insertAdjacentHTML('afterend', `
            <div class="map">
                <iframe src="${item.maps_url}" width="90%" height="400" frameborder="0"></iframe>
                <button onclick="window.open('${item.maps_url}')">Get Directions</button>
            </div>
        `);
    }
 }