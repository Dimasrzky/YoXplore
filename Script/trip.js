window.loadTrips = function(section = 'YoTrip') {
    console.log('Loading trips for section:', section);
    
    const tbody = document.querySelector('#tripTable tbody');
    if (!tbody) {
        console.error('Trip table body not found');
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
                            <button class="btn btn-warning btn-sm me-2" onclick="editTrip(${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteTrip(${item.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
        });
};
 
 // Load saat halaman dibuka jika di tab YoTrip
 document.addEventListener('DOMContentLoaded', function() {
    const yotripTab = document.querySelector('#yotrip');
    if (yotripTab && yotripTab.classList.contains('active')) {
        loadTrips();
    }
 });
window.showAddTripModal = function() {
    // Debug log
    console.log('Opening Trip modal...');
    
    const form = document.getElementById('addTripForm');
    if (!form) {
        console.error('Trip form not found!');
        return;
    }
    
    form.reset();
    const idInput = form.querySelector('input[name="id"]');
    if (idInput) idInput.remove();
    
    // Load categories dengan YoTrip type
    const categorySelect = form.querySelector('select[name="category"]');
    if (categorySelect) {
        fetch('../Controller/get_categories.php?type=YoTrip')
            .then(response => response.json())
            .then(result => {
                console.log('Categories loaded:', result); // Debug log
                if (result.success) {
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    result.data.forEach(category => {
                        categorySelect.innerHTML += `
                            <option value="${category.id}">${category.name}</option>
                        `;
                    });
                }
            })
            .catch(error => console.error('Error loading categories:', error));
    }
    
    const modal = new bootstrap.Modal(document.getElementById('addTripModal'));
    modal.show();
};

function loadTripCategories() {
    const categorySelect = document.querySelector('#addTripForm select[name="category"]');
    if (!categorySelect) return;

    fetch('../Controller/get_categories.php?type=YoTrip')
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

window.saveTrip = function() {
    const form = document.getElementById('addTripForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const isEdit = form.querySelector('input[name="id"]') ? true : false;

    fetch(isEdit ? '../Controller/update_destination.php' : '../Controller/add_destination_yotrip.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addTripModal'));
            if (modal) modal.hide();
            
            form.reset();
            const idInput = form.querySelector('input[name="id"]');
            if (idInput) idInput.remove();
            
            loadTrips('YoTrip');
            alert(isEdit ? 'Trip destination berhasil diupdate' : 'Trip destination berhasil ditambahkan');
        } else {
            throw new Error(result.message || 'Gagal ' + (isEdit ? 'mengupdate' : 'menambahkan') + ' trip destination');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message);
    });
};

window.editTrip = function(id) {
    // Load categories terlebih dahulu
    loadTripCategories();  // Panggil ini dulu
    
    fetch(`../Controller/get_destination_detail.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                const form = document.getElementById('addTripForm');
                
                // Tunggu sebentar agar kategori selesai dimuat
                setTimeout(() => {
                    form.querySelector('select[name="category"]').value = data.category_id;
                    form.querySelector('input[name="name"]').value = data.name;
                    form.querySelector('input[name="address"]').value = data.address;
                    form.querySelector('input[name="openTime"]').value = data.opening_hours;
                    form.querySelector('input[name="closeTime"]').value = data.closing_hours;
                    form.querySelector('input[name="phone"]').value = data.phone || '';
                    
                    // Tambahkan input hidden untuk id jika belum ada
                    if (!form.querySelector('input[name="id"]')) {
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    form.querySelector('input[name="id"]').value = id;
                }, 500);
                
                const modal = new bootstrap.Modal(document.getElementById('addTripModal'));
                modal.show();
            }
        })
        .catch(error => console.error('Error:', error));
};

window.deleteTrip = function(id) {
    if (confirm('Apakah Anda yakin ingin menghapus trip destination ini?')) {
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
                alert('Trip destination berhasil dihapus');
                loadTrips('YoTrip');
            } else {
                alert('Gagal menghapus trip destination: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus trip destination');
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#yotrip' || document.querySelector('#yotrip.active')) {
        window.loadTrips('YoTrip');
    }
});
