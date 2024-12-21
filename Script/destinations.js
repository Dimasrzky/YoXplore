document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addDestinationModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', loadCategories);
    }

    // Load initial data
    loadDestinations('YoStay');
});

// Fungsi untuk menampilkan modal Add Destination
function showAddDestinationModal() {
    const modal = new bootstrap.Modal(document.getElementById('addDestinationModal'));
    
    // Load kategori sebelum modal ditampilkan
    loadCategories();
    
    // Tampilkan modal
    modal.show();
}

// Fungsi untuk load kategori
function loadCategories() {
    const categorySelect = document.querySelector('select[name="category"]');
    if (!categorySelect) {
        console.error('Category select element not found');
        return;
    }

    fetch('../Controller/get_categories.php?type=YoStay')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                result.data.forEach(category => {
                    categorySelect.innerHTML += `
                        <option value="${category.id}">${category.name}</option>
                    `;
                });
            } else {
                console.error('Failed to load categories:', result.message);
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

// Fungsi untuk menyimpan destination baru
function saveDestination() {
    const form = document.getElementById('addDestinationForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    
    fetch('../Controller/add_destination_yostay.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addDestinationModal'));
            modal.hide();
            form.reset();
            loadDestinations('YoStay');
            alert('Destinasi berhasil ditambahkan');
        } else {
            throw new Error(result.message || 'Gagal menambahkan destinasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menambahkan destinasi: ' + error.message);
    });
}

// Event listener saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener untuk tombol Add Destination
    const addButton = document.querySelector('[data-action="addDestination"]');
    if (addButton) {
        addButton.addEventListener('click', showAddDestinationModal);
    }
});