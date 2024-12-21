function loadCategories() {
    const categorySelect = document.querySelector('select[name="category"]');
    if (!categorySelect) {
        console.error('Elemen select category tidak ditemukan');
        return;
    }

    fetch('../Controller/get_categories.php?type=YoStay')
        .then(response => response.json())
        .then(result => {
            console.log('Response kategori:', result); // untuk debug
            if (result.success) {
                categorySelect.innerHTML = '<option value="">Pilih Kategori</option>';
                
                result.data.forEach(category => {
                    categorySelect.innerHTML += `
                        <option value="${category.id}">${category.name}</option>
                    `;
                });
            } else {
                console.error('Gagal memuat kategori:', result.message);
            }
        })
        .catch(error => {
            console.error('Error saat memuat kategori:', error);
        });
}

function saveDestination() {
    const form = document.getElementById('destinationForm');
    const formData = new FormData(form);
    
    // Convert section name untuk match dengan ENUM di database
    const section = formData.get('section');
    formData.set('feature_type', section.charAt(0).toUpperCase() + section.slice(1));
    
    fetch('../Controller/save_destination.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if(result.success) {
            loadDestinations(section);
            const modal = bootstrap.Modal.getInstance(document.getElementById('destinationModal'));
            modal.hide();
            form.reset();
            alert('Destination saved successfully');
        } else {
            alert('Failed to save destination: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving destination');
    });
}

function editDestination(id, section) {
    fetch(`../Controller/get_destination.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                const data = result.data;
                const form = document.getElementById('destinationForm');
                
                form.querySelector('[name="destinationId"]').value = data.id;
                form.querySelector('[name="name"]').value = data.name;
                form.querySelector('[name="address"]').value = data.address;
                form.querySelector('[name="openTime"]').value = data.opening_hours;
                form.querySelector('[name="section"]').value = section;
                
                // Show current image if exists
                if(data.id) {
                    document.getElementById('currentImage').innerHTML = `
                        <img src="../Controller/get_image.php?id=${data.id}" 
                             alt="Current image" 
                             class="img-thumbnail" 
                             style="height: 100px; object-fit: cover;">
                    `;
                }
                
                new bootstrap.Modal(document.getElementById('destinationModal')).show();
            } else {
                alert('Failed to load destination data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading destination data');
        });
}

function deleteDestination(id, section) {
    if(confirm('Are you sure you want to delete this destination?')) {
        fetch('../Controller/delete_destination.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                loadDestinations(section);
                alert('Destination deleted successfully');
            } else {
                alert('Failed to delete destination: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting destination');
        });
    }
}

function renderDestinations(section) {
    const destinations = JSON.parse(localStorage.getItem(section)) || [];
    const tbody = document.querySelector(`#${section} table tbody`);
    tbody.innerHTML = '';
    
    destinations.forEach(dest => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><img src="${dest.image}" alt="${dest.name}" class="img-thumbnail" style="width: 50px; height: 50px"></td>
            <td>${dest.name}</td>
            <td>${dest.address}</td>
            <td>${dest.openTime}</td>
            <td>
                <button class="btn btn-sm btn-warning me-2" onclick="openDestinationModal('${section}', ${dest.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteDestination('${section}', ${dest.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}
document.getElementById('addDestinationModal').addEventListener('show.bs.modal', loadCategories);