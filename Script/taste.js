window.loadTastePlaces = function(section = 'YoTaste') {
    console.log('Loading taste places for:', section);
    
    const tbody = document.querySelector('#tasteTable tbody');
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
                                 style="width: 50px; height: 50px; object-fit: cover;"
                                 onerror="this.src='../Image/placeholder.jpg'">
                        </td>
                        <td>${item.category_name}</td>
                        <td>${item.name}</td>
                        <td>${item.address}</td>
                        <td>${item.phone || '-'}</td>
                        <td>${item.opening_hours || '-'}</td>
                        <td>${item.closing_hours || '-'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2" onclick="editTastePlace(${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteTastePlace(${item.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No data available</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading taste places:', error);
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Error: ${error.message}</td></tr>`;
        });
};

window.showAddTastePlaceModal = function() {
    const form = document.getElementById('addTastePlaceForm');
    form.reset();
    const idInput = form.querySelector('input[name="id"]');
    if (idInput) idInput.remove();
    
    loadTasteCategories();
    const modal = new bootstrap.Modal(document.getElementById('addTastePlaceModal'));
    modal.show();
};

function loadTasteCategories() {
    const categorySelect = document.querySelector('#addTastePlaceForm select[name="category"]');
    if (!categorySelect) return;

    fetch('../Controller/get_categories.php?type=YoTaste')
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

window.saveTastePlace = function() {
    const form = document.getElementById('addTastePlaceForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.set('feature_type', 'YoTaste'); // Pastikan feature_type terisi

    // Debug log
    console.log('Saving taste place...');
    
    fetch('../Controller/add_destination_yotaste.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        console.log('Save result:', result);
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addTastePlaceModal'));
            if (modal) modal.hide();
            
            form.reset();
            loadTastePlaces('YoTaste');
            alert('Restaurant berhasil ditambahkan');
        } else {
            throw new Error(result.message || 'Gagal menambahkan restaurant');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menambahkan restaurant: ' + error.message);
    });
};

window.editTastePlace = function(id) {
    // Load categories terlebih dahulu
    loadTasteCategories();  // Panggil ini dulu
    
    fetch(`../Controller/get_destination_detail.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                const form = document.getElementById('addTastePlaceForm');
                
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
                
                const modal = new bootstrap.Modal(document.getElementById('addTastePlaceModal'));
                modal.show();
            }
        })
        .catch(error => console.error('Error:', error));
};

window.deleteTastePlace = function(id) {
    if (confirm('Apakah Anda yakin ingin menghapus restaurant ini?')) {
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
                alert('Item berhasil dihapus');
                loadTastePlaces();
            } else {
                alert('Gagal menghapus Item: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus Item');
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const yotasteTab = document.querySelector('a[href="#yotaste"]');
    if (yotasteTab) {
        yotasteTab.addEventListener('shown.bs.tab', function() {
            loadTastePlaces('YoTaste');
        });
    }
    
    // Load data jika sedang di tab YoTaste
    if (document.querySelector('#yotaste.active')) {
        loadTastePlaces('YoTaste');
    }
});