function fetchUsers() {
    fetch('../Controller/get_users.php')
        .then(response => {
            // Validasi response terlebih dahulu
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (err) {
                    console.error('Raw response:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(result => {
            if (result.success) {
                // Update total users
                const totalElement = document.getElementById('totalUsers');
                if (totalElement) {
                    totalElement.textContent = result.count || 0;
                }

                // Update table
                const tbody = document.querySelector('#usersTable tbody');
                if (tbody) {
                    tbody.innerHTML = '';
                    
                    if (Array.isArray(result.data) && result.data.length > 0) {
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
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center">No users found</td>
                            </tr>
                        `;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const tbody = document.querySelector('#usersTable tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            ${error.message || 'Error loading users'}
                        </td>
                    </tr>
                `;
            }
        });
}

window.editUser = function(id, username, email) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

window.deleteUser = function(id) {
    if (confirm('Are you sure you want to delete this user?')) {
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
                fetchUsers();
            } else {
                alert('Error deleting user');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

window.updateUser = function() {
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

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    fetchUsers();
    setInterval(fetchUsers, 3000);
});