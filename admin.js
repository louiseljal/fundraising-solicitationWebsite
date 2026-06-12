document.addEventListener('DOMContentLoaded', () => {
    loadAdminDashboard();
});

// Open campaign creation modal
function openCreateModal() {
    const modal = document.getElementById('campaignModal');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        alert('Campaign modal not found. Please ensure the modal element exists in the page.');
    }
}

// Fetch all solicitations for the Admin view
function loadAdminDashboard() {
    fetch('api/solicitations.php')
        .then(response => {
            // Check if the backend rejected the request (e.g., 401 Unauthorized or 403 Forbidden)
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                populateMetrics(data.solicitations);
                renderSolicitationQueue(data.solicitations);
            } else if (data.message && data.message.includes('Authentication required')) {
                // Redirect to login if not authenticated
                window.location.href = 'login.html';
            } else {
                alert("API Error: " + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error("Error loading dashboard data:", err);
            const tbody = document.getElementById('solicitationQueueRows');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle-fill"></i> Failed to connect to backend: ${err.message}</td></tr>`;
            }
        });
}

// Calculate top card metrics dynamically
function populateMetrics(list) {
    const total = list.length;
    const pending = list.filter(item => item.status === 'Pending').length;
    const approved = list.filter(item => item.status === 'Approved' || item.status === 'Completed').length;

    document.getElementById('metric-total').innerText = total;
    document.getElementById('metric-pending').innerText = pending;
    document.getElementById('metric-approved').innerText = approved;
}

// Render entries into rows
function renderSolicitationQueue(list) {
    const tbody = document.getElementById('solicitationQueueRows');
    if (!tbody) return;

    const pendingList = list.filter(item => item.status === 'Pending');
    
    if (pendingList.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No pending solicitations found.</td></tr>`;
        return;
    }

    tbody.innerHTML = ''; // Clear loader
    pendingList.forEach(item => {
        let urgencyClass = 'bg-secondary';
        if (item.urgency_level === 'High') urgencyClass = 'bg-danger';
        if (item.urgency_level === 'Medium') urgencyClass = 'bg-warning text-dark';
        if (item.urgency_level === 'Low') urgencyClass = 'bg-info text-dark';

        const actionButtons = `
            <button class="btn btn-sm btn-success me-1" onclick="updatePostStatus(${item.solicitation_id}, 'Approved')">
                <i class="bi bi-check-lg"></i> Approve
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="updatePostStatus(${item.solicitation_id}, 'Rejected')">
                <i class="bi bi-x-lg"></i> Reject
            </button>
        `;

        const row = `
            <tr>
                <td>
                    <div class="fw-bold">${item.post_title}</div>
                    <small class="text-muted">by ${item.first_name || ''} ${item.last_name || ''}</small>
                </td>
                <td><span class="badge bg-light text-dark border">${item.solicitation_category || 'General'}</span></td>
                <td><span class="badge ${urgencyClass}">${item.urgency_level || 'Medium'}</span></td>
                <td class="text-end fw-bold text-success">₱${parseFloat(item.target_amount || 0).toLocaleString()}</td>
                <td class="text-center">${actionButtons}</td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', row);
    });
}

// Send Status Update Requests directly to api/solicitations.php
// Add or replace this at the bottom of admin.js
function updatePostStatus(id, newStatus) {
    const formData = new FormData();
    formData.append('action', 'update_status'); 
    formData.append('solicitation_id', id);
    formData.append('status', newStatus); // This will pass 'Approved'

    fetch('api/solicitations.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Post Approved! Redirecting to public feed...");
            window.location.href = 'solicitations.html'; // Takes you to see it shared live
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => console.error("Error updating post:", err));
}