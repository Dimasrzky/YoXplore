document.addEventListener('DOMContentLoaded', function() {
    const itemId = new URLSearchParams(window.location.search).get('id');
    
    if (!itemId) {
        showError('No item ID provided');
        return;
    }
 
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
    // Update main item details
    document.querySelector('.column-right h1').textContent = data.item.name;
    document.querySelector('.rating-score').textContent = data.item.rating || '0.0';
    document.querySelector('.rating-user').textContent = `From ${data.item.total_reviews || 0} users`;
    
    // Update info section
    document.querySelector('.location-info .info-item:nth-child(1) p').textContent = data.item.address;
    document.querySelector('.location-info .info-item:nth-child(2) p').textContent = 
        `${data.item.opening_hours} - ${data.item.closing_hours}`;
    document.querySelector('.location-info .info-item:nth-child(3) p').textContent = data.item.phone;

    // Update gallery
    const gallery = document.querySelector('.gallery');
    data.images.forEach((url, index) => {
        const galleryItem = gallery.children[index];
        if (galleryItem) {
            galleryItem.querySelector('img').src = url;
        }
    });

    // Update map
    const mapFrame = document.querySelector('.map iframe');
    const routeBtn = document.querySelector('.route-btn');
    if (data.item.maps_url) {
        mapFrame.src = data.item.maps_url;
        routeBtn.onclick = () => window.open(data.item.maps_url);
    }

    // Update modal gallery
    const modalGallery = document.querySelector('.modal-gallery');
    data.images.forEach((url, index) => {
        const img = modalGallery.children[index];
        if (img) {
            img.src = url;
        }
    });
}