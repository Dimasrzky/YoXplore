window.loadConcerts = function(section = 'YoConcert') {
    console.log('Loading concerts for:', section);
    
    const tbody = document.querySelector('#concertTable tbody');
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
                        <td>${item.phone || '-'}</td>
                        <td>${item.opening_hours || '-'}</td>
                        <td>${item.closing_hours || '-'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2" onclick="editConcert(${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteConcert(${item.id})">
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

window.showAddConcertModal = function() {
    const form = document.getElementById('addConcertForm');
    // Reset form dan hapus input id jika ada
    form.reset();
    const idInput = form.querySelector('input[name="id"]');
    if (idInput) idInput.remove();
    
    // Load categories
    loadConcertCategories();
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addConcertModal'));
    modal.show();
}

function loadConcertCategories() {
    const categorySelect = document.querySelector('#addConcertForm select[name="category"]');
    if (!categorySelect) return;

    fetch('../Controller/get_categories.php?type=YoConcert')
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
window.saveConcert = function() {
    const form = document.getElementById('addConcertForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    // Cek apakah ini operasi edit atau add baru
    const isEdit = form.querySelector('input[name="id"]') ? true : false;

    // Log untuk debugging
    console.log('Operation:', isEdit ? 'Edit' : 'Add New');
    console.log('Form data:', Object.fromEntries(formData));

    fetch(isEdit ? '../Controller/update_destination.php' : '../Controller/add_destination_yoshow.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addConcertModal'));
            modal.hide();
            
            // Reset form dan hapus input id jika ada
            form.reset();
            const idInput = form.querySelector('input[name="id"]');
            if (idInput) idInput.remove();
            
            loadConcerts();
            alert(isEdit ? 'Concert venue berhasil diupdate' : 'Concert venue berhasil ditambahkan');
        } else {
            throw new Error(result.message || 'Gagal ' + (isEdit ? 'mengupdate' : 'menambahkan') + ' concert venue');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
    });
}

window.editConcert = function(id) {
    // Load categories terlebih dahulu
    loadConcertCategories();  // Panggil ini dulu
    
    // Kemudian ambil data concert
    fetch(`../Controller/get_destination_detail.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                const form = document.getElementById('addConcertForm');
                
                // Tunggu sebentar agar kategori selesai dimuat
                setTimeout(() => {
                    form.querySelector('select[name="category"]').value = data.category_id;
                    form.querySelector('input[name="name"]').value = data.name;
                    form.querySelector('input[name="address"]').value = data.address;
                    form.querySelector('input[name="openTime"]').value = data.opening_hours;
                    form.querySelector('input[name="closeTime"]').value = data.closing_hours;
                    form.querySelector('input[name="phone"]').value = data.phone || '';
                    
                    if (!form.querySelector('input[name="id"]')) {
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    form.querySelector('input[name="id"]').value = id;
                }, 500);
                
                const modal = new bootstrap.Modal(document.getElementById('addConcertModal'));
                modal.show();
            }
        })
        .catch(error => console.error('Error:', error));
};

window.deleteConcert = function(id) {
    if (confirm('Apakah Anda yakin ingin menghapus concert venue ini?')) {
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
                alert('Concert venue berhasil dihapus');
                loadConcerts();
            } else {
                alert('Gagal menghapus concert venue: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus concert venue');
        });
    }
};

// Initialize when document loads
document.addEventListener('DOMContentLoaded', function() {
    const yoconcertTab = document.querySelector('#yoconcert');
    if (yoconcertTab && yoconcertTab.classList.contains('active')) {
        loadConcerts();
    }
});