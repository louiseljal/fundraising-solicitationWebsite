document.addEventListener('DOMContentLoaded', () => {
    loadAdminDashboard();
});

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
                renderQueueTable(data.solicitations);
            } else {
                alert("API Error: " + data.message);
            }
        })
        .catch(err => {
            console.error("Error loading dashboard data:", err);
            const tbody = document.getElementById('admin-queue-tbody');
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle-fill"></i> Failed to connect to backend: ${err.message}</td></tr>`;
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
function renderQueueTable(list) {
    const tbody = document.getElementById('admin-queue-tbody');
    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No solicitation records found.</td></tr>`;
        return;
    }

    tbody.innerHTML = ''; // Clear loader
    list.forEach(item => {
        let badgeClass = 'bg-secondary';
        if (item.status === 'Pending') badgeClass = 'bg-warning text-dark';
        if (item.status === 'Approved') badgeClass = 'bg-success';
        if (item.status === 'Completed') badgeClass = 'bg-info text-dark';

        // Action controls change visibility states contextually
        const actionButtons = item.status === 'Pending' ? `
            <button class="btn btn-sm btn-success me-1" onclick="updatePostStatus(${item.solicitation_id}, 'Approved')">
                <i class="bi bi-check-lg"></i> Approve
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="updatePostStatus(${item.solicitation_id}, 'Rejected')">
                <i class="bi bi-x-lg"></i> Reject
            </button>
        ` : `
            <span class="text-muted small"><i class="bi bi-lock-fill"></i> Managed</span>
        `;

        const row = `
            <tr>
                <td>
                    <div class="fw-bold">${item.first_name} ${item.last_name}</div>
                    <small class="text-muted font-monospace">@${item.username}</small>
                </td>
                <td>
                    <div class="fw-bold">${item.post_title}</div>
                    <span class="badge bg-light text-dark border">${item.solicitation_category}</span>
                </td>
                <td class="fw-bold text-success">₱${parseFloat(item.target_amount).toLocaleString()}</td>
                <td><small>${item.campaign_deadline}</small></td>
                <td><span class="badge ${badgeClass}">${item.status}</span></td>
                <td class="text-end">${actionButtons}</td>
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