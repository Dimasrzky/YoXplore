function loadDestinations(section) {
    console.log('Loading destinations for:', section);
    const tbody = document.querySelector(`#${section}Table tbody`);
    if (!tbody) {
        console.error('Table body not found for section:', section);
        return;
    }

    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';

    fetch(`../Controller/get_destinations.php?section=${section}`)
        .then(response => response.json())
        .then(result => {
            console.log('Destinations data:', result);
            if (result.success && result.data) {
                tbody.innerHTML = '';
                
                if (result.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No destinations found</td></tr>';
                    return;
                }

                result.data.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <img src="${item.image_url || '../Image/placeholder.jpg'}" 
                                 alt="${item.name}" 
                                 class="img-thumbnail" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>${item.name}</td>
                        <td>${item.address}</td>
                        <td>${item.opening_hours || '-'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2" onclick="editDestination('${section}', ${item.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteDestination('${section}', ${item.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load data</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading data</td></tr>';
        });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Handle tab changes
    const triggerTabList = document.querySelectorAll('[data-bs-toggle="pill"]');
    triggerTabList.forEach(triggerEl => {
        triggerEl.addEventListener('shown.bs.tab', event => {
            const targetId = event.target.getAttribute('href').slice(1);
            if (targetId !== 'users') {
                loadDestinations(targetId);
            }
        });
    });

    // Load initial content if not on users tab
    const activeTab = document.querySelector('.nav-link.active');
    if (activeTab) {
        const targetId = activeTab.getAttribute('href').slice(1);
        if (targetId !== 'users') {
            loadDestinations(targetId);
        }
    }
});