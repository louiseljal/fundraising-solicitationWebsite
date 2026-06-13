<?php require_once 'api/admin_protect.php'; ?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="d-flex">
        <aside class="sidebar p-3 text-white" style="min-height: 100vh; width: 260px;">
            <div class="sidebar-logo d-flex align-items-center mb-4 px-2">
                <i class="bi bi-shield-lock-fill me-2 fs-4 text-success"></i>
                <span class="fw-bold lh-sm">Admin Panel <br><small class="fw-normal text-white-50">Workspace Suite</small></span>
            </div>
            
            <nav class="nav flex-column gap-1">
                <span class="text-uppercase text-white-50 small px-2 mb-1 sidebar-section-header" style="font-size: 0.75rem; letter-spacing: 0.05em;">Dashboards</span>
                <a class="nav-link text-start" href="admin.php">
                    <i class="bi bi-lightning-charge-fill me-2"></i> Quick Overview
                </a>
                <a class="nav-link text-start" href="analytics.php">
                    <i class="bi bi-pie-chart-fill me-2"></i> Performance Analytics
                </a>
                
                <span class="text-uppercase text-white-50 small px-2 mt-4 mb-1 sidebar-section-header" style="font-size: 0.75rem; letter-spacing: 0.05em;">Operations</span>
                <a class="nav-link active text-start" href="manage_campaigns.php">
                    <i class="bi bi-megaphone-fill me-2"></i> Manage Campaigns
                </a>
                <a class="nav-link text-start" href="queues.php">
                    <i class="bi bi-inboxes-fill me-2"></i> Verification Queues
                </a>
                <a class="nav-link text-start" href="membership.php">
                    <i class="bi bi-people-fill me-2"></i> User Membership
                </a>
                <a class="nav-link text-start" href="activity_log.php">
                    <i class="bi bi-journal-check me-2"></i> Activity Logs
                </a>
                
            </nav>
            
                        <div class="sidebar-footer pt-3 border-top border-secondary-subtle">
                <span class="text-uppercase text-white-50 small px-2 mb-1 d-block sidebar-section-header">Settings</span>
                
                <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                
                
            </div>
        </aside>

        <main class="flex-grow-1 p-4" style="background-color: var(--bg-main); height: 100vh; overflow-y: auto;">
            
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--text-color);">Campaign Workspace</h4>
                    <p class="text-muted small mb-0">Initialize fundraising routes and modify configuration attributes.</p>
                </div>
            </header>

            <div class="stat-card border-0 rounded-4 p-4 shadow-sm bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-folder-fill text-success me-2"></i> Platform Funding Targets</h5>
                    <button class="btn btn-brand btn-sm rounded-pill px-3 py-1.5 fw-semibold text-white shadow-sm align-self-start align-self-md-auto" onclick="openCreateModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add New Campaign
                    </button>
                </div>

                <div class="row g-3 mb-3 p-3 bg-light rounded-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="campaignSearch" class="form-control border-start-0 small" placeholder="Search campaign titles or details..." oninput="triggerSearchFilter()">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted small fw-semibold text-secondary">Status Filter</span>
                            <select id="statusFilter" class="form-select border-start-0 small" onchange="triggerStatusFilter()">
                                <option value="All">All Registered Statuses</option>
                                <option value="Active">Active</option>
                                <option value="Draft">Draft</option>
                                <option value="Paused">Paused</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small text-uppercase">
                            <tr>
                                <th scope="col" style="width: 5%">ID</th>
                                <th scope="col" style="width: 45%">Campaign Metadata Scope</th>
                                <th scope="col" style="width: 15%" class="text-end">Goal Matrix</th>
                                <th scope="col" style="width: 15%" class="text-end">Current Cache</th>
                                <th scope="col" style="width: 20%" class="text-center">Controls</th>
                            </tr>
                        </thead>
                        <tbody class="small" id="campaignCrudRows">
                            <tr><td colspan="5" class="text-center py-4 text-muted">Awaiting connection to data streams...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-3 pt-3 border-top border-light">
                    <div id="paginationInfo" class="small text-muted fw-medium">
                        Showing 0 to 0 of 0 records
                    </div>
                    <nav aria-label="Campaign records step navigation">
                        <ul class="pagination pagination-sm mb-0 rounded-pill shadow-none" id="paginationControls">
                        </ul>
                    </nav>
                </div>

            </div>
        </main>
    </div>

    <div class="modal fade" id="campaignModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0 pt-4 px-4 justify-content-between">
                    <h5 class="modal-title fw-bold text-success" id="campaignModalTitle">Create Campaign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="campaignStructureForm" onsubmit="handleCampaignFormSubmit(event)">
                    <div class="modal-body p-4">
                        <input type="hidden" id="form_campaign_id">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Campaign Title Focus</label>
                            <input type="text" class="form-control rounded-3" id="form_title" required>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Category</label>
                                <select id="form_category" class="form-select rounded-3" required>
                                    <option value="Disaster Relief">Disaster Relief</option>
                                    <option value="Medical">Medical</option>
                                    <option value="Education">Education</option>
                                    <option value="Animal Welfare">Animal Welfare</option>
                                    <option value="Community">Community</option>
                                    <option value="Environment">Environment</option>
                                    <option value="Arts & Culture">Arts & Culture</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Financial Target (₱)</label>
                                <input type="number" step="0.01" class="form-control rounded-3" id="form_goal" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Start Date</label>
                                <input type="date" class="form-control rounded-3" id="form_start_date" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">End Date</label>
                                <input type="date" class="form-control rounded-3" id="form_end_date" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Operational Status</label>
                                <select id="form_status" class="form-select rounded-3">
                                    <option value="Draft">Draft</option>
                                    <option value="Active">Active</option>
                                    <option value="Paused">Paused</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Context Narrative Scope</label>
                            <textarea id="form_description" class="form-control rounded-3" rows="3" required></textarea>
                        </div>

                        <hr class="my-4">
                        
                        <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger-subtle">
                            <label class="form-label small fw-bold text-danger"><i class="bi bi-shield-lock-fill me-1"></i> Admin Authorization Required</label>
                            <input type="password" class="form-control border-danger-subtle rounded-3" id="form_admin_password" placeholder="Enter your admin password to save changes" required>
                        </div>

                    </div>
                    <div class="modal-footer border-0 pt-0 p-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill text-white px-4 fw-semibold"><i class="bi bi-save me-1"></i> Authorize & Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill text-danger me-2 fs-4"></i> Confirm Deletion Authorization
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetDeleteAction()"></button>
                </div>
                <div class="modal-body px-4">
                    <p class="text-muted small mb-3">Archiving/Deleting a campaign is a highly privileged action. Please enter your administrator password to proceed.</p>
                    <div class="mb-2">
                        <label for="admin-confirm-password" class="form-label small fw-bold text-secondary">Your Admin Password</label>
                        <input type="password" class="form-control rounded-3" id="admin-confirm-password" placeholder="Enter password to authorize">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal" onclick="resetDeleteAction()">Cancel</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-semibold" onclick="verifyAndDelete()">Verify & Archive</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const campaignModalObj = new bootstrap.Modal(document.getElementById('campaignModal'));
        const passwordModalObj = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
        
        const formatCurrency = (val) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);
        const escapeHTML = (str) => str ? str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';

        let masterCampaignsArray = [];
        let filteredCampaignsArray = [];
        let currentPage = 1;
        const recordsPerPage = 10;
        let pendingDeleteId = null;

        function loadCampaignWorkspace() {
            // Note: Now pointing to api/campaigns.php
            fetch('api/campaigns.php')
                .then(res => res.json())
                .then(data => {
                    masterCampaignsArray = data.campaigns || [];
                    applyFiltersAndPagination();
                }).catch(err => console.error("Database connection fault:", err));
        }

        function applyFiltersAndPagination() {
            const searchVal = document.getElementById('campaignSearch').value.toLowerCase().trim();
            const statusFilterVal = document.getElementById('statusFilter').value;

            filteredCampaignsArray = masterCampaignsArray.filter(c => {
                const matchesSearch = c.title.toLowerCase().includes(searchVal) || 
                                      (c.description && c.description.toLowerCase().includes(searchVal));
                const matchesStatus = (statusFilterVal === 'All') || (c.campaign_status === statusFilterVal);
                return matchesSearch && matchesStatus;
            });

            const totalRecords = filteredCampaignsArray.length;
            const totalPages = Math.ceil(totalRecords / recordsPerPage) || 1;

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIdx = (currentPage - 1) * recordsPerPage;
            const endIdx = Math.min(startIdx + recordsPerPage, totalRecords);
            
            const renderSlice = filteredCampaignsArray.slice(startIdx, endIdx);
            const rowContainer = document.getElementById('campaignCrudRows');
            rowContainer.innerHTML = '';

            if (renderSlice.length === 0) {
                rowContainer.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No records registered matching target requirements.</td></tr>`;
                document.getElementById('paginationInfo').innerText = 'Showing 0 to 0 of 0 records';
                document.getElementById('paginationControls').innerHTML = '';
                return;
            }

            renderSlice.forEach(c => {
                let statusBadge = '';
                switch(c.campaign_status) {
                    case 'Active': statusBadge = 'bg-success-subtle text-success'; break;
                    case 'Draft': statusBadge = 'bg-warning-subtle text-warning-emphasis'; break;
                    case 'Paused': statusBadge = 'bg-info-subtle text-info-emphasis'; break;
                    default: statusBadge = 'bg-secondary-subtle text-dark';
                }

                // Injecting all needed fields for the Edit parameters
                rowContainer.innerHTML += `
                    <tr>
                        <td class="text-muted font-monospace">#${c.campaign_id}</td>
                        <td>
                            <div class="fw-bold text-dark mb-1">${escapeHTML(c.title)} <span class="badge ${statusBadge} rounded-pill px-2 py-0.5 small ms-1" style="font-size:0.7rem;">${escapeHTML(c.campaign_status)}</span></div>
                            <small class="text-muted d-inline-block text-truncate" style="max-width: 380px;"><span class="fw-bold me-1">[${escapeHTML(c.category)}]</span> ${escapeHTML(c.description)}</small>
                        </td>
                        <td class="text-end fw-semibold text-dark">${formatCurrency(c.goal_amount)}</td>
                        <td class="text-end text-success fw-bold">${formatCurrency(c.current_raised_cache)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-light border-secondary-subtle rounded-3 px-2 py-1 me-1 text-primary" onclick="openEditModal(${c.campaign_id}, '${escapeHTML(c.title)}', ${c.goal_amount}, '${escapeHTML(c.description)}', '${escapeHTML(c.campaign_status)}', '${escapeHTML(c.category)}', '${c.start_date}', '${c.end_date}')"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn btn-sm btn-light border-danger-subtle rounded-3 px-2 py-1 text-danger" onclick="executeArchiveLink(${c.campaign_id})"><i class="bi bi-archive-fill"></i></button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('paginationInfo').innerText = `Showing ${totalRecords === 0 ? 0 : startIdx + 1} to ${endIdx} of ${totalRecords} records`;
            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            const container = document.getElementById('paginationControls');
            container.innerHTML = '';
            if (totalPages <= 1) return;

            let prevDisabled = (currentPage === 1) ? 'disabled' : '';
            container.innerHTML += `<li class="page-item ${prevDisabled}"><a class="page-item page-link border-0 px-2" href="#" onclick="changePage(${currentPage - 1}, event)"><i class="bi bi-chevron-left"></i></a></li>`;

            for (let i = 1; i <= totalPages; i++) {
                let activeClass = (i === currentPage) ? 'active bg-success text-white' : 'text-dark bg-white';
                container.innerHTML += `<li class="page-item"><a class="page-link border-0 mx-1 px-3 rounded-pill fw-semibold ${activeClass}" href="#" onclick="changePage(${i}, event)">${i}</a></li>`;
            }

            let nextDisabled = (currentPage === totalPages) ? 'disabled' : '';
            container.innerHTML += `<li class="page-item ${nextDisabled}"><a class="page-item page-link border-0 px-2" href="#" onclick="changePage(${currentPage + 1}, event)"><i class="bi bi-chevron-right"></i></a></li>`;
        }

        function changePage(targetPage, event) {
            if (event) event.preventDefault();
            currentPage = targetPage;
            applyFiltersAndPagination();
        }

        function triggerSearchFilter() { currentPage = 1; applyFiltersAndPagination(); }
        function triggerStatusFilter() { currentPage = 1; applyFiltersAndPagination(); }

        // --- CRUD LOGIC AND API HOOKS ---

        function openCreateModal() {
            document.getElementById('campaignStructureForm').reset();
            document.getElementById('form_campaign_id').value = '';
            document.getElementById('form_admin_password').value = '';
            document.getElementById('campaignModalTitle').innerText = "Initialize System Campaign";
            campaignModalObj.show();
        }

        function openEditModal(id, title, goal, desc, status, category, start, end) {
            document.getElementById('campaignModalTitle').innerText = "Modify Campaign Attributes";
            document.getElementById('form_campaign_id').value = id;
            document.getElementById('form_title').value = title;
            document.getElementById('form_goal').value = goal;
            document.getElementById('form_category').value = category;
            document.getElementById('form_start_date').value = start;
            document.getElementById('form_end_date').value = end;
            document.getElementById('form_status').value = status;
            document.getElementById('form_description').value = desc;
            document.getElementById('form_admin_password').value = ''; // Always require fresh password
            campaignModalObj.show();
        }

        // Submits Create / Update requests with Password Authorization
        function handleCampaignFormSubmit(e) {
            e.preventDefault();
            
            const pwd = document.getElementById('form_admin_password').value;
            if (!pwd.trim()) {
                alert("Administrator password is required to save changes.");
                return;
            }

            const id = document.getElementById('form_campaign_id').value;
            const action = id ? 'update' : 'create';

            const payload = new FormData();
            if(id) payload.append('campaign_id', id);
            payload.append('title', document.getElementById('form_title').value);
            payload.append('goal_amount', document.getElementById('form_goal').value);
            payload.append('description', document.getElementById('form_description').value);
            payload.append('campaign_status', document.getElementById('form_status').value);
            payload.append('category', document.getElementById('form_category').value);
            payload.append('start_date', document.getElementById('form_start_date').value);
            payload.append('end_date', document.getElementById('form_end_date').value);
            payload.append('admin_password', pwd);

            fetch(`api/campaigns.php?action=${action}`, { 
                method: 'POST', 
                body: payload 
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) { 
                    alert("Campaign updated securely.");
                    campaignModalObj.hide(); 
                    loadCampaignWorkspace(); 
                } else {
                    alert("Verification error: " + (data.message || 'Action refused.'));
                }
            })
            .catch(err => {
                console.error("Network communication error:", err);
                alert("Communication issue with server.");
            });
        }

        // Prepares Delete/Archive action and opens Password confirmation modal
        function executeArchiveLink(id) {
            pendingDeleteId = id;
            document.getElementById('admin-confirm-password').value = '';
            passwordModalObj.show();
        }

        function verifyAndDelete() {
            const pwd = document.getElementById('admin-confirm-password').value;
            if(!pwd.trim()) {
                alert('Administrator password required to delete campaign.');
                return;
            }

            const payload = new FormData();
            payload.append('campaign_id', pendingDeleteId);
            payload.append('admin_password', pwd);

            fetch('api/campaigns.php?action=delete', { 
                method: 'POST', 
                body: payload 
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Campaign archived securely.");
                    passwordModalObj.hide();
                    resetDeleteAction();
                    loadCampaignWorkspace();
                } else {
                    alert("Verification error: " + (data.message || 'Action refused.'));
                    document.getElementById('admin-confirm-password').value = '';
                }
            })
            .catch(err => {
                console.error("Network error:", err);
                alert("Communication issue with server.");
            });
        }

        function resetDeleteAction() {
            pendingDeleteId = null;
            document.getElementById('admin-confirm-password').value = '';
        }

        document.addEventListener("DOMContentLoaded", loadCampaignWorkspace);

        // Get today's local date in YYYY-MM-DD format
        const today = new Date().toLocaleDateString('en-CA');
        
        // Select every date input element on the page
        const dateInputs = document.querySelectorAll('input[type="date"]');
        
        // Loop through each input to apply the constraints
        dateInputs.forEach(input => {
            input.value = today;
            input.min = today;
        });
    </script>

    <script src="main.js"></script>
</body>
</html>