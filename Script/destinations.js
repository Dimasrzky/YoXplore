document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addDestinationModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', loadCategories);
    }

    // Load initial data
    loadDestinations('YoStay');
});

// Event listener saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener untuk tombol Add Destination
    const addButton = document.querySelector('[data-action="addDestination"]');
    if (addButton) {
        addButton.addEventListener('click', showAddDestinationModal);
    }
});

// destinations.js
window.loadDestinations = function(section = 'YoStay') {
    console.log('Loading destinations for:', section);
    const tbody = document.querySelector('#destinationsTable tbody');
    if (!tbody) {
        console.error('Table body tidak ditemukan');
        return;
    }

    fetch(`../Controller/get_destinations.php?section=${section}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && Array.isArray(result.data)) {
                tbody.innerHTML = '';
                
                result.data.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <img src="data:image/jpeg;base64,${item.main_image}" 
                                 alt="${item.name}" 
                                 class="img-thumbnail" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>${item.name}</td>
                        <td>${item.address}</td>
                        <td>${item.opening_hours || '-'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2" onclick="editDestination(${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteDestination(${item.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error('Error:', error));
};

window.showAddDestinationModal = function() {
    const modal = new bootstrap.Modal(document.getElementById('addDestinationModal'));
    loadCategories();
    modal.show();
};

window.saveDestination = function() {
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
            if (modal) modal.hide();
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
};

function loadCategories() {
    const categorySelect = document.querySelector('select[name="category"]');
    if (!categorySelect) return;

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
            }
        })
        .catch(error => console.error('Error:', error));
}

// Fungsi untuk menampilkan modal Add Destination
function showAddDestinationModal() {
    const modal = new bootstrap.Modal(document.getElementById('addDestinationModal'));
    
    // Load kategori sebelum modal ditampilkan
    loadCategories();
    
    // Tampilkan modal
    modal.show();
}
