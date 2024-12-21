function loadDestinations(section = 'YoStay') {
    const tbody = document.querySelector('#destinationsTable tbody');
    if (!tbody) {
        console.error('Table body tidak ditemukan');
        return;
    }

    fetch(`../Controller/get_destinations.php?section=${section}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && Array.isArray(result.data)) {
                tbody.innerHTML = '';  // Clear existing content
                
                result.data.forEach(item => {
                    const tr = document.createElement('tr');
                    // ... rest of your code
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

window.saveDestination = function() {
    const form = document.getElementById('addDestinationForm');
    if (!form) {
        console.error('Form tidak ditemukan');
        return;
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    // Debug: log form data
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch('../Controller/add_destination_yostay.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        console.log('Raw response:', text); // Debug
        
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Invalid server response: ' + text);
        }
    })
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addDestinationModal'));
            if (modal) modal.hide();
            form.reset();
            loadDestinations();
            alert('Destinasi berhasil ditambahkan');
        } else {
            throw new Error(result.message || 'Gagal menambahkan destinasi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menambahkan destinasi: ' + (error.message || error));
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

// Panggil loadCategories saat modal dibuka
document.getElementById('addDestinationModal').addEventListener('show.bs.modal', loadCategories);

// Initialize when document loads
document.addEventListener('DOMContentLoaded', function() {
    loadDestinations();
});

window.showAddDestinationModal = function() {
    const modal = new bootstrap.Modal(document.getElementById('addDestinationModal'));
    modal.show();
}