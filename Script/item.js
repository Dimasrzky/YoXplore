document.addEventListener('DOMContentLoaded', function() {
    // Get item ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');
    
    if (!itemId) {
        showError('ID item tidak ditemukan');
        return;
    }

    // Load item details
    fetch(`../Controller/get_item_detail.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Replace content
                document.querySelector('.main').outerHTML = data.html;
                
                // Initialize gallery modal
                setupGalleryModal(data.data.images);
                
                // Initialize review modal
                setupReviewModal(itemId);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Gagal memuat detail item');
        });
});

function setupGalleryModal(images) {
    const modalGallery = document.querySelector('.modal-gallery');
    if (modalGallery) {
        modalGallery.innerHTML = images.map(img => `
            <img src="data:image/jpeg;base64,${img.image_url}" alt="Gallery Image">
        `).join('');
    }
}

function setupReviewModal(itemId) {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const fileInput = document.getElementById('file-input');
    const imagePreview = document.getElementById('image-preview');
    const stars = document.querySelectorAll('.rating .star');
    
    if (openModalBtn) {
        openModalBtn.addEventListener('click', () => {
            modal.classList.add('open');
        });
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            modal.classList.remove('open');
            resetReviewForm();
        });
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', handleImagePreview);
    }
    
    if (stars) {
        stars.forEach((star, index) => {
            star.addEventListener('click', () => setRating(index + 1));
        });
    }
}

function handleImagePreview(event) {
    const imagePreview = document.getElementById('image-preview');
    imagePreview.innerHTML = '';
    
    Array.from(event.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            imagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

function setRating(rating) {
    const stars = document.querySelectorAll('.rating .star');
    const ratingInput = document.querySelector('.rating input');
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.replace('bx-star', 'bxs-star');
            star.classList.add('active');
        } else {
            star.classList.replace('bxs-star', 'bx-star');
            star.classList.remove('active');
        }
    });
    
    ratingInput.value = rating;
}

function resetReviewForm() {
    const stars = document.querySelectorAll('.rating .star');
    const ratingInput = document.querySelector('.rating input');
    const textarea = document.querySelector('textarea');
    const imagePreview = document.getElementById('image-preview');
    
    stars.forEach(star => {
        star.classList.replace('bxs-star', 'bx-star');
        star.classList.remove('active');
    });
    
    ratingInput.value = '';
    textarea.value = '';
    imagePreview.innerHTML = '';
}

function showError(message) {
    const errorDialog = document.createElement('div');
    errorDialog.className = 'error-dialog';
    errorDialog.innerHTML = `
        <div class="error-content">
            <p>${message}</p>
            <button onclick="this.parentElement.parentElement.remove()">OK</button>
        </div>
    `;
    document.body.appendChild(errorDialog);
}