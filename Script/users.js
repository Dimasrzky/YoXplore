// User-related functions
function fetchUsers() {
    fetch('../Controller/get_users.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Update total users
                document.getElementById('totalUsers').textContent = result.count;
                
                // Update table
                const tbody = document.querySelector('#usersTable tbody');
                tbody.innerHTML = '';
                
                result.data.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-2" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                console.error('Failed to fetch users:', result.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateTotalUsers() { 
    fetch('../Controller/get_users.php')
        .then(response => response.json())
        .then(data => {
            // Update total users sesuai jumlah data dari database
            document.getElementById('totalUsers').textContent = data.length;
        })
        .catch(error => console.error('Error:', error));
}

function saveUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    
    const user = {
        id: Date.now(),
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password')
    };
    
    users.push(user);
    localStorage.setItem('users', JSON.stringify(users));
    
    renderUsers();
    updateTotalUsers();
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
    modal.hide();
    form.reset();
}

function editUser(id, username, email) { 
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;

    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(id) {if (confirm('Are you sure you want to delete this user?')) {
    fetch('../Controller/delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchUsers(); // Refresh user list
        } else {
            alert('Error deleting user');
        }
    })
    .catch(error => console.error('Error:', error));
}
}

function updateUser() {
    const id = document.getElementById('editUserId').value;
    const username = document.getElementById('editUsername').value;
    const email = document.getElementById('editEmail').value;
    const password = document.getElementById('editPassword').value;

    const data = {
        id: id,
        username: username,
        email: email
    };
    
    if(password) {
        data.password = password;
    }

    fetch('../Controller/update_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if(result.success) {
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            renderUsers(); // Refresh tabel
            alert('User updated successfully');
        } else {
            alert('Failed to update user: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating user');
    });
}

// Script/users.js
function renderUsers() {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) {
        console.error('Table body element not found');
        return;
    }

    tbody.innerHTML = '';
    
    fetch('../Controller/get_users.php')
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                // Update total users count
                const totalUsers = document.getElementById('totalUsers');
                if (totalUsers) {
                    totalUsers.textContent = result.count || 0;
                }
                
                // Render table data
                result.data.forEach(user => {
                    const date = new Date(user.created_at);
                    const formattedDate = `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td>${formattedDate}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2" onclick="editUser(${user.id}, '${user.username}', '${user.email}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No users found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading users. Please try again.</td></tr>';
        });
}

// Fungsi untuk semua event listener
function initializeUserEvents() {
    // Initial render
    renderUsers();
    
    // Auto refresh
    setInterval(renderUsers, 3000);
    
    // Form submit handlers
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.onsubmit = (e) => {
            e.preventDefault();
            saveUser();
        };
    }
}

// Initialize when script loads
document.addEventListener('DOMContentLoaded', initializeUserEvents);