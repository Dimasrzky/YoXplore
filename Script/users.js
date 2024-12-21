let usersTable = null;

function initializeUsers() {
    usersTable = document.querySelector('#usersTable tbody');
    if (!usersTable) {
        console.error('Users table not found');
        return;
    }
    fetchUsers();
    setInterval(fetchUsers, 3000);
}

function fetchUsers() {
    fetch('../Controller/get_users.php')
        .then(response => response.json())
        .then(result => {
            console.log('Data users:', result);
            
            if (result.success) {
                // Update total users
                const totalElement = document.getElementById('totalUsers');
                if (totalElement) {
                    totalElement.textContent = result.count;
                }
                
                // Update table
                if (usersTable) {
                    usersTable.innerHTML = '';
                    
                    if (Array.isArray(result.data)) {
                        result.data.forEach(user => {
                            const tr = document.createElement('tr');
                            const date = new Date(user.created_at).toLocaleDateString();
                            tr.innerHTML = `
                                <td>${user.username}</td>
                                <td>${user.email}</td>
                                <td>${date}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm me-2" onclick="editUser(${user.id}, '${user.username}', '${user.email}')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            `;
                            usersTable.appendChild(tr);
                        });
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
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
    tbody.innerHTML = '';
    
    fetch('../Controller/get_users.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(user => {
                // Format tanggal
                const createdAt = new Date(user.created_at).toLocaleDateString();
                
                // Buat baris tabel untuk setiap user
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${createdAt}</td>
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
        })
        .catch(error => console.error('Error:', error));
}