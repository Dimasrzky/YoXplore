window.loadTaste = function(section = 'YoTaste') {
    console.log('Loading concerts for:', section);
    
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
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 onerror="this.src='../Image/placeholder.jpg'">
                        </td>
                        <td>${item.category_name}</td>
                        <td>${item.name}</td>
                        <td>${item.address}</td>
                        <td>${item.opening_hours || '-'}</td>
                        <td>${item.closing_hours || '-'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2" onclick="editTaste(${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteTaste(${item.id})">
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

window.showAddTasteModal = function() {
    loadTasteCategories();
    const modal = new bootstrap.Modal(document.getElementById('addTasteModal'));
    modal.show();
};

function loadTasteCategories() {
    const categorySelect = document.querySelector('#addTasteForm select[name="category"]');
    if (!categorySelect) return;

    // Tambahkan type=YoConcert di URL
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

window.saveTaste = function() {
    const form = document.getElementById('addTasteForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const isEdit = formData.get('id') ? true : false;

    fetch(isEdit ? '../Controller/update_destination.php' : '../Controller/add_destination_yotaste.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addTasteModal'));
            modal.hide();
            form.reset();
            loadTaste();
            alert(isEdit ? 'Warung berhasil diupdate' : 'Item Taste berhasil ditambahkan');
        } else {
            throw new Error(result.message || 'Gagal ' + (isEdit ? 'mengupdate' : 'menambahkan') + ' Warung');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
    });
};

window.editTaste = function(id) {
    fetch(`../Controller/get_destination_detail.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                const form = document.getElementById('addTasteForm');
                
                form.querySelector('select[name="category"]').value = data.category_id;
                form.querySelector('input[name="name"]').value = data.name;
                form.querySelector('input[name="address"]').value = data.address;
                form.querySelector('input[name="openTime"]').value = data.opening_hours;
                form.querySelector('input[name="closeTime"]').value = data.closing_hours;
                
                if (!form.querySelector('input[name="id"]')) {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    form.appendChild(idInput);
                }
                form.querySelector('input[name="id"]').value = id;
                
                const modal = new bootstrap.Modal(document.getElementById('addTasteModal'));
                modal.show();
            }
        })
        .catch(error => console.error('Error:', error));
};

window.deleteTaste = function(id) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
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
                loadTaste();
            } else {
                alert('Gagal menghapus item: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus item');
        });
    }
};

// Initialize when document loads
document.addEventListener('DOMContentLoaded', function() {
    const yotasteTab = document.querySelector('#yotaste');
    if (yotasteTab && yotasteTab.classList.contains('active')) {
        loadConcerts();
    }
});