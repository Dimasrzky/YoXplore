// File: review.js

// Handle review submission
document.querySelector('.submit').addEventListener('click', async function(e) {
    e.preventDefault();
    
    // Get form values
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
    
    // Create FormData object
    const formData = new FormData();
    formData.append('item_id', window.location.search.split('=')[1]); // Get item_id from URL
    formData.append('rating', rating);
    formData.append('comment', comment);
    
    // Append each selected file
    if (fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('images[]', fileInput.files[i]);
        }
    }

    try {
        const response = await fetch('../Controller/save_review.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Review submitted successfully!');
            document.querySelector('.modal').classList.remove('open');
            resetForm();
            window.location.reload(); // Reload page to show new review
        } else {
            alert(data.message || 'Error submitting review');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error submitting review');
    }
});

// Handle image preview
document.getElementById('file-input').addEventListener('change', function() {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = ''; // Clear previous previews
    
    if (this.files) {
        Array.from(this.files).forEach(file => {
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image">&times;</button>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Reset form after submission
function resetForm() {
    document.querySelector('textarea').value = '';
    document.querySelector('[name="rating"]').value = '';
    document.getElementById('file-input').value = '';
    document.getElementById('image-preview').innerHTML = '';
    
    // Reset stars
    const stars = document.querySelectorAll('.rating .star');
    stars.forEach(star => {
        star.classList.replace('bxs-star', 'bx-star');
    });
}

// Handle star rating
const allStars = document.querySelectorAll('.rating .star');
const ratingInput = document.querySelector('.rating input');

allStars.forEach((star, idx) => {
    star.addEventListener('click', function() {
        ratingInput.value = idx + 1;
        allStars.forEach((s, index) => {
            if (index <= idx) {
                s.classList.replace('bx-star', 'bxs-star');
            } else {
                s.classList.replace('bxs-star', 'bx-star');
            }
        });
    });
});