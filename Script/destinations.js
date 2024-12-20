function loadDestinations(section) {
    console.log('Loading section:', section); // Debug log

    fetch(`../Controller/get_destinations.php?section=${section}`)
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            return response.json();
        })
        .then(result => {
            console.log('Data received:', result); // Debug log
            
            const tbody = document.querySelector(`#${section} table tbody`);
            tbody.innerHTML = ''; // Clear existing content

            if (!result.data || result.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">No data available</td>
                    </tr>`;
                return;
            }

            result.data.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <img src="../Controller/get_image.php?id=${item.id}" 
                             alt="${item.name}" 
                             class="img-thumbnail" 
                             style="width: 50px; height: 50px">
                    </td>
                    <td>${item.name}</td>
                    <td>${item.address}</td>
                    <td>${item.opening_hours || '-'}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-warning" 
                                    onclick="editDestination(${item.id}, '${section}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="deleteDestination(${item.id}, '${section}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error loading destinations:', error);
            const tbody = document.querySelector(`#${section} table tbody`);
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Error loading data. Please try again.
                    </td>
                </tr>`;
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