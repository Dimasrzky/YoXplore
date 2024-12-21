function fetchUsers() {
    fetch('../Controller/get_users.php')
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Raw response:', text);
                throw new Error('Server response was not valid JSON');
            }
        })
        .then(result => {
            if (!result.success) {
                throw new Error(result.message || 'Failed to fetch users');
            }

            // Update total
            const totalElement = document.getElementById('totalUsers');
            if (totalElement) {
                totalElement.textContent = result.count || 0;
            }

            // Update table
            const tbody = document.querySelector('#usersTable tbody');
            if (!tbody) return;

            tbody.innerHTML = '';

            if (!Array.isArray(result.data) || result.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center">No users found</td>
                    </tr>`;
                return;
            }

            result.data.forEach(user => {
                const date = new Date(user.created_at).toLocaleDateString();
                tbody.innerHTML += `
                    <tr>
                        <td>${escapeHtml(user.username)}</td>
                        <td>${escapeHtml(user.email)}</td>
                        <td>${date}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2" onclick="editUser(${user.id}, '${escapeHtml(user.username)}', '${escapeHtml(user.email)}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>`;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            const tbody = document.querySelector('#usersTable tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            ${escapeHtml(error.message)}
                        </td>
                    </tr>`;
            }
        });
}

// Helper function untuk escape HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Panggil fetchUsers setiap 5 detik
document.addEventListener('DOMContentLoaded', () => {
    fetchUsers();
    setInterval(fetchUsers, 3000);
});

// Expose functions untuk event handlers
function editUser(id, username, email) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;

    // Clear password field
    document.getElementById('editPassword').value = '';

    // Get modal instance
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    
    // Add click handler to save button
    const saveButton = document.querySelector('#editUserModal .btn-primary');
    if(saveButton) {
        // Remove old handlers
        saveButton.replaceWith(saveButton.cloneNode(true));
        // Add new handler
        document.querySelector('#editUserModal .btn-primary').addEventListener('click', updateUser);
    }
    
    // Show modal
    modal.show();
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
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Raw response:', text);
                throw new Error('Invalid server response');
            }
        })
        .then(result => {
            if (!result.success) {
                throw new Error(result.message || 'Failed to delete user');
            }
            // Refresh data setelah delete berhasil
            fetchUsers();
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Failed to delete user');
        });
    }
};

// Di users.js
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
    
    if(password.trim() !== '') {
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
            // Tutup modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            if(modal) {
                modal.hide();
            }
            // Refresh tabel
            fetchUsers();
            alert('User updated successfully');
        } else {
            throw new Error(result.message || 'Failed to update user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Error updating user');
    });
};