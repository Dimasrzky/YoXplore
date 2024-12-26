document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal');
    const galleryModal = document.getElementById('gallery-modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const stars = document.querySelectorAll('.rating .star');
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('image-preview');

    // Modal handlers
    openModalBtn.onclick = () => modal.style.display = 'flex';
    closeModalBtn.onclick = () => modal.style.display = 'none';
    
    // Gallery modal
    window.openGallery = () => galleryModal.style.display = 'block';
    document.querySelector('.close').onclick = () => galleryModal.style.display = 'none';

    // Star rating logic
    stars.forEach((star, index) => {
        star.onclick = () => {
            document.querySelector('input[name="rating"]').value = index + 1;
            stars.forEach((s, i) => {
                s.classList.toggle('bxs-star', i <= index);
                s.classList.toggle('bx-star', i > index);
            });
        };
    });

    // Image preview logic
    fileInput.onchange = () => {
        previewContainer.innerHTML = '';
        [...fileInput.files].forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    };

    // Close modals when clicking outside
    window.onclick = (event) => {
        if (event.target === modal) modal.style.display = 'none';
        if (event.target === galleryModal) galleryModal.style.display = 'none';
    };
});