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
                            <img src="data:image/jpeg;base64,${item.main_image || ''}" 
                                 alt="${item.name}" 
                                 class="img-thumbnail" 
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 onerror="this.src='../Image/placeholder.jpg'">
                        </td>
                        <td>${item.category_name}</td>
                        <td>${item.name}</td>
                        <td>${item.address}</td>
                        <td>${item.opening_hours || '-'}</td>
                        <td>${item.closing_hours || '-'}</td>
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
    const isEdit = formData.get('id') ? true : false;

    fetch(isEdit ? '../Controller/update_destination.php' : '../Controller/add_destination_yostay.php', {
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
            alert(isEdit ? 'Destinasi berhasil diupdate' : 'Destinasi berhasil ditambahkan');
        } else {
            throw new Error(result.message || 'Gagal ' + (isEdit ? 'mengupdate' : 'menambahkan') + ' destinasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
    });
}

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

// Fungsi untuk mengedit destinasi
window.editDestination = function(id) {
    fetch(`../Controller/get_destination_detail.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                
                // Isi form dengan data yang ada
                const form = document.getElementById('addDestinationForm');
                form.querySelector('select[name="category"]').value = data.category_id;
                form.querySelector('input[name="name"]').value = data.name;
                form.querySelector('input[name="address"]').value = data.address;
                form.querySelector('input[name="openTime"]').value = data.opening_hours;
                form.querySelector('input[name="closeTime"]').value = data.closing_hours;
                
                // Tambahkan ID untuk update
                if (!form.querySelector('input[name="id"]')) {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    form.appendChild(idInput);
                }
                form.querySelector('input[name="id"]').value = id;
                
                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('addDestinationModal'));
                modal.show();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Fungsi untuk menghapus destinasi
window.deleteDestination = function(id) {
    if (confirm('Apakah Anda yakin ingin menghapus destinasi ini?')) {
        fetch('../Controller/delete_destination.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Destinasi berhasil dihapus');
                loadDestinations('YoStay'); // Reload tabel
            } else {
                alert('Gagal menghapus destinasi: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus destinasi');
        });
    }
}