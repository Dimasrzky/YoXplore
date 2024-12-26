// item.js
document.addEventListener('DOMContentLoaded', fetchItemDetails);

function fetchItemDetails() {
    const itemId = new URLSearchParams(window.location.search).get('id');
    if (!itemId) {
        showError('No item ID provided');
        return;
    }

    showLoading();
    fetch(`../Controller/get_destination_detail.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (!data || !data.item) throw new Error('Invalid data');
            renderItemDetails(data);
        })
        .catch(error => {
            hideLoading();
            showError(error.message);
        });
}

function renderItemDetails(data) {
    const item = data.item;

    // Basic info
    document.getElementById('itemName').textContent = item.name;
    document.querySelector('.rating-score').textContent = item.rating;
    document.querySelector('.rating-user').textContent = `From ${item.total_reviews} users`;
    document.querySelector('.star-rating').innerHTML = generateStars(item.rating);

    // Location info
    const locationInfo = document.querySelector('.location-info');
    locationInfo.innerHTML = `
        <div class="info-item">
            <span class="icon"><img src="../Image/location.png" alt=""></span>
            <p>${item.address}</p>
        </div>
        <div class="info-item">
            <span class="icon"><img src="../Image/clock.png" alt=""></span>
            <p>${item.opening_hours} - ${item.closing_hours}</p>
        </div>
        <div class="info-item">
            <span class="icon"><img src="../Image/call.png" alt=""></span>
            <p>${item.phone}</p>
        </div>
    `;

    // Gallery
    renderGallery(data.images);

    // Map
    if (item.maps_url) {
        document.querySelector('.map iframe').src = item.maps_url;
        document.querySelector('.route-btn').onclick = () => window.open(item.maps_url);
    }
}