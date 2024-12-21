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
    const updateBtn = document.querySelector('#editUserModal .btn-primary');
    if (updateBtn) {
        updateBtn.removeAttribute('onclick');
    }
    fetchUsers();
    setInterval(fetchUsers, 3000);
});

function initializeUpdateForm() {
    const form = document.getElementById('editUserForm');
    const submitButton = document.querySelector('#editUserModal .btn-primary');
    
    if (submitButton) {
        const newButton = submitButton.cloneNode(true);
        submitButton.parentNode.replaceChild(newButton, submitButton);
        newButton.addEventListener('click', function() {
            updateUser();
        });
    }
}

function editUser(userId) {
    fetch(`../Controller/get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const user = result.data;
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editEmail').value = user.email;
                
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
                
                initializeUpdateForm();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Expose functions untuk event handlers
function editUser(id, username, email) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;
    document.getElementById('editPassword').value = '';
    
    initializeUpdateForm();
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(userId) {
    if (!userId || isNaN(userId)) {
        console.error('Invalid user ID:', userId);
        return;
    }

    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        fetch('../Controller/delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: userId })
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid JSON');
            }
        })
        .then(result => {
            if (result.success) {
                alert('User berhasil dihapus');
                fetchUsers(); // Reload daftar user
            } else {
                throw new Error(result.message || 'Failed to delete user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus user: ' + error.message);
        });
    }
}

function updateUser() {
    // Prevent double submission
    const submitButton = document.querySelector('#editUserModal .btn-primary');
    if (submitButton) {
        submitButton.disabled = true;
    }

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
            // Close modal first
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            
            // Then update table
            fetchUsers();
            
            // Single alert
            setTimeout(() => alert('User updated successfully'), 100);
        } else {
            throw new Error(result.message || 'Failed to update user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating user');
    })
    .finally(() => {
        // Re-enable submit button
        if (submitButton) {
            submitButton.disabled = false;
        }
    });
}