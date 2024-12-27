// Add this to your item.js file
document.addEventListener('DOMContentLoaded', function() {
    // Initialize star rating
    initStarRating();
    
    // Initialize form submission
    initReviewSubmission();
    
    // Initialize image preview
    initImagePreview();
});

function initStarRating() {
    const stars = document.querySelectorAll('.rating .star');
    const ratingInput = document.querySelector('[name="rating"]');
    
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            ratingInput.value = index + 1;
            updateStars(index);
        });
    });
}

function updateStars(selectedIndex) {
    const stars = document.querySelectorAll('.rating .star');
    stars.forEach((star, index) => {
        if (index <= selectedIndex) {
            star.classList.remove('bx-star');
            star.classList.add('bxs-star');
        } else {
            star.classList.remove('bxs-star');
            star.classList.add('bx-star');
        }
    });
}

function initReviewSubmission() {
    const submitBtn = document.querySelector('.submit');
    const modal = document.getElementById('modal');
    
    submitBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        console.log('Submit button clicked');

        // Get form data
        const rating = document.querySelector('[name="rating"]').value;
        const comment = document.querySelector('textarea').value;
        const fileInput = document.getElementById('file-input');
        
        // Validate input
        if (!rating) {
            alert('Please select a rating');
            return;
        }
        
        if (!comment.trim()) {
            alert('Please write a review');
            return;
        }
        
        // Create FormData
        const formData = new FormData();
        formData.append('item_id', getItemIdFromUrl());
        formData.append('rating', rating);
        formData.append('comment', comment);
        
        // Append images if any
        if (fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach(file => {
                formData.append('images[]', file);
            });
        }

        try {
            console.log('Sending review data...');
            const response = await fetch('../Controller/save_review.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('Response:', data);
            
            if (data.success) {
                alert('Review submitted successfully!');
                modal.classList.remove('open');
                resetForm();
                location.reload(); // Reload to show new review
            } else {
                alert(data.message || 'Error submitting review');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error submitting review. Please try again.');
        }
    });
}

function initImagePreview() {
    const fileInput = document.getElementById('file-input');
    const preview = document.getElementById('image-preview');
    
    fileInput.addEventListener('change', function() {
        preview.innerHTML = '';
        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
}

function resetForm() {
    document.querySelector('textarea').value = '';
    document.querySelector('[name="rating"]').value = '';
    document.getElementById('file-input').value = '';
    document.getElementById('image-preview').innerHTML = '';
    updateStars(-1);
}

function getItemIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}