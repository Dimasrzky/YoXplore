function loadDestinations(section = 'YoStay') {
    fetch(`../Controller/get_destinations.php?section=${section}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const tbody = document.querySelector('#destinationsTable tbody');
                tbody.innerHTML = '';
                
                result.data.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <img src="${item.main_image || '../Image/placeholder.jpg'}" 
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
}

function saveDestination(formData) {
    fetch('../Controller/add_destination.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('addDestinationModal')).hide();
            loadDestinations('YoStay');
            alert('Destination added successfully');
        } else {
            throw new Error(result.message || 'Failed to add destination');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add destination: ' + error.message);
    });
}

function submitDestination() {
    const form = document.getElementById('addDestinationForm');
    const formData = new FormData(form);
    
    // Validasi form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    saveDestination(formData);
}

// Load categories when modal opens
document.getElementById('addDestinationModal').addEventListener('show.bs.modal', function() {
    loadCategories();
});

function loadCategories() {
    fetch('../Controller/get_categories.php?type=YoStay')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const select = document.querySelector('[name="category_id"]');
                select.innerHTML = '<option value="">Select Category</option>';
                result.data.forEach(category => {
                    select.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                });
            }
        });
}

// Initialize when document loads
document.addEventListener('DOMContentLoaded', function() {
    loadDestinations();
});