<?php require_once 'api/admin_protect.php'; ?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Verification Queues</title>
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
                <a class="nav-link text-start" href="manage_campaigns.php">
                    <i class="bi bi-megaphone-fill me-2"></i> Manage Campaigns
                </a>
                <a class="nav-link active text-start" href="queues.php">
                    <i class="bi bi-inboxes-fill me-2"></i> Verification Queues
                </a>
                <a class="nav-link text-start" href="membership.php">
                    <i class="bi bi-people-fill me-2"></i> User Membership
                </a>
                <a class="nav-link text-start" href="activity_log.php">
                    <i class="bi bi-journal-check me-2"></i> Activity Logs
                </a>
                <a class="nav-link" href="index.html"><i class="bi bi-house-door-fill me-2"></i> Home</a>
            </nav>

            <div class="sidebar-footer pt-3 border-top border-secondary-subtle">
                <span class="text-uppercase text-white-50 small px-2 mb-1 d-block sidebar-section-header">Settings</span>
                
                <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                
                
            </div>
        </aside>

        <main class="flex-grow-1 p-4" style="background-color: var(--bg-main); height: 100vh; overflow-y: auto;">
            <header class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--text-color);">Verification Hub</h4>
                    <p class="text-muted small mb-0">Review inbound donations and pending draft campaigns before activation.</p>
                </div>
                <button class="btn btn-outline-secondary rounded-pill px-3 py-1" onclick="loadVerificationQueues()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Queues
                </button>
            </header>

            <div class="stat-card border-0 rounded-4 p-4 shadow-sm bg-white mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cash-stack text-success me-2"></i> Inbound Donation Clearances</h6>
                    <div class="input-group w-auto">
                        <span class="input-group-text bg-light border-end-0 text-muted py-1"><i class="bi bi-search small"></i></span>
                        <input type="text" id="searchDonations" class="form-control form-control-sm bg-light border-start-0 py-1" placeholder="Search reference or name..." oninput="filterDonations()">
                    </div>
                </div>

                <div class="table-responsive border rounded-3">
                    <table class="table align-middle text-dark small mb-0">
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                <th>Ref Code</th>
                                <th>Contributor</th>
                                <th>Target Route</th>
                                <th class="text-end">Value</th>
                                <th class="text-center">Action Flags</th>
                            </tr>
                        </thead>
                        <tbody id="donationVerificationRows">
                            <tr><td colspan="5" class="text-center text-muted py-4">Connecting...</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light">
                    <div id="donationsPageInfo" class="small text-muted fw-medium"></div>
                    <ul class="pagination pagination-sm mb-0 rounded-pill" id="donationsPaginationControls"></ul>
                </div>
            </div>

            <div class="stat-card border-0 rounded-4 p-4 shadow-sm bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i> Pending Draft Campaigns Queue</h6>
                    <div class="input-group w-auto">
                        <span class="input-group-text bg-light border-end-0 text-muted py-1"><i class="bi bi-search small"></i></span>
                        <input type="text" id="searchCampaigns" class="form-control form-control-sm bg-light border-start-0 py-1" placeholder="Search title..." oninput="filterCampaigns()">
                    </div>
                </div>

                <div class="table-responsive border rounded-3">
                    <table class="table align-middle text-dark small mb-0">
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                <th>Campaign Title & Detail</th>
                                <th>Category</th>
                                <th>Start Date</th>
                                <th class="text-end">Target Goal</th>
                                <th class="text-center">Review Triggers</th>
                            </tr>
                        </thead>
                        <tbody id="campaignFeedRows">
                            <tr><td colspan="5" class="text-center text-muted py-4">Connecting...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light">
                    <div id="campaignsPageInfo" class="small text-muted fw-medium"></div>
                    <ul class="pagination pagination-sm mb-0 rounded-pill" id="campaignsPaginationControls"></ul>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const formatCurrency = (val) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);
        const escapeHTML = (str) => str ? str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;") : '';

        const recordsPerPage = 10;
        let allDonations = [], filteredDonations = [], donationPage = 1;
        let allCampaigns = [], filteredCampaigns = [], campaignPage = 1;

        function loadVerificationQueues() {
            fetch('api/queues_backend.php')
                .then(res => res.json())
                .then(data => {
                    allDonations = data.pendingTransactions || [];
                    allCampaigns = data.pendingCampaigns || [];
                    filterDonations();
                    filterCampaigns();
                })
                .catch(err => console.error(err));
        }

        function filterDonations() {
            const query = document.getElementById('searchDonations').value.toLowerCase().trim();
            filteredDonations = allDonations.filter(d => 
                (d.transaction_reference && d.transaction_reference.toLowerCase().includes(query)) ||
                (d.username && d.username.toLowerCase().includes(query)) ||
                (d.campaign_title && d.campaign_title.toLowerCase().includes(query))
            );
            donationPage = 1;
            renderDonations();
        }

        function renderDonations() {
            const totalRecords = filteredDonations.length;
            const totalPages = Math.ceil(totalRecords / recordsPerPage) || 1;
            if (donationPage > totalPages) donationPage = totalPages;
            if (donationPage < 1) donationPage = 1;

            const startIdx = (donationPage - 1) * recordsPerPage;
            const endIdx = Math.min(startIdx + recordsPerPage, totalRecords);
            const slice = filteredDonations.slice(startIdx, endIdx);

            const tbody = document.getElementById('donationVerificationRows');
            tbody.innerHTML = '';

            if (slice.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No pending donations.</td></tr>`;
                document.getElementById('donationsPageInfo').innerText = 'Showing 0 to 0 of 0 records';
                document.getElementById('donationsPaginationControls').innerHTML = '';
                return;
            }

            slice.forEach(t => {
                tbody.innerHTML += `
                    <tr>
                        <td class="font-monospace text-muted">#${t.transaction_reference}</td>
                        <td><strong>${escapeHTML(t.username)}</strong></td>
                        <td>${escapeHTML(t.campaign_title)}</td>
                        <td class="text-end fw-bold text-success">${formatCurrency(t.amount)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-success rounded-pill px-3 py-1 me-1" onclick="processTx(${t.donation_id}, 'Approve')">Approve</button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="processTx(${t.donation_id}, 'Reject')">Deny</button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('donationsPageInfo').innerText = `Showing ${startIdx + 1} to ${endIdx} of ${totalRecords} records`;
            buildPagination('donationsPaginationControls', totalPages, donationPage, 'changeDonationPage');
        }

        function changeDonationPage(page, event) {
            if(event) event.preventDefault();
            donationPage = page;
            renderDonations();
        }

        function processTx(id, action) {
            fetch('api/queues_backend.php?action=process_transaction', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ donation_id: id, status_action: action })
            })
            .then(res => res.json())
            .then(data => { if(data.success) loadVerificationQueues(); });
        }

        function filterCampaigns() {
            const query = document.getElementById('searchCampaigns').value.toLowerCase().trim();
            filteredCampaigns = allCampaigns.filter(c => 
                (c.title && c.title.toLowerCase().includes(query)) ||
                (c.category && c.category.toLowerCase().includes(query))
            );
            campaignPage = 1;
            renderCampaigns();
        }

        function renderCampaigns() {
            const totalRecords = filteredCampaigns.length;
            const totalPages = Math.ceil(totalRecords / recordsPerPage) || 1;
            if (campaignPage > totalPages) campaignPage = totalPages;
            if (campaignPage < 1) campaignPage = 1;

            const startIdx = (campaignPage - 1) * recordsPerPage;
            const endIdx = Math.min(startIdx + recordsPerPage, totalRecords);
            const slice = filteredCampaigns.slice(startIdx, endIdx);

            const tbody = document.getElementById('campaignFeedRows');
            tbody.innerHTML = '';

            if (slice.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No draft campaigns.</td></tr>`;
                document.getElementById('campaignsPageInfo').innerText = 'Showing 0 to 0 of 0 records';
                document.getElementById('campaignsPaginationControls').innerHTML = '';
                return;
            }

            slice.forEach(c => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${escapeHTML(c.title)}</strong><br><small class="text-muted">${escapeHTML(c.description.substring(0, 50))}...</small></td>
                        <td><span class="badge bg-light text-dark border">${escapeHTML(c.category)}</span></td>
                        <td class="text-muted">${c.start_date}</td>
                        <td class="text-end fw-bold">${formatCurrency(c.goal_amount)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary rounded-pill px-3 py-1 me-1" onclick="processCampaign(${c.campaign_id}, 'Approve')">Activate</button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" onclick="processCampaign(${c.campaign_id}, 'Reject')">X</button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('campaignsPageInfo').innerText = `Showing ${startIdx + 1} to ${endIdx} of ${totalRecords} records`;
            buildPagination('campaignsPaginationControls', totalPages, campaignPage, 'changeCampaignPage');
        }

        function changeCampaignPage(page, event) {
            if(event) event.preventDefault();
            campaignPage = page;
            renderCampaigns();
        }

        function processCampaign(id, action) {
            fetch('api/queues_backend.php?action=process_campaign', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ campaign_id: id, status_action: action })
            })
            .then(res => res.json())
            .then(data => { if(data.success) loadVerificationQueues(); });
        }

        function buildPagination(containerId, totalPages, currentPage, changeFunc) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (totalPages <= 1) return;

            container.innerHTML += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="${changeFunc}(${currentPage - 1}, event)">&laquo;</a></li>`;
            for (let i = 1; i <= totalPages; i++) {
                container.innerHTML += `<li class="page-item"><a class="page-link ${i === currentPage ? 'active' : ''}" href="#" onclick="${changeFunc}(${i}, event)">${i}</a></li>`;
            }
            container.innerHTML += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="${changeFunc}(${currentPage + 1}, event)">&raquo;</a></li>`;
        }

        document.addEventListener("DOMContentLoaded", loadVerificationQueues);
    </script>

    <script src="main.js"></script>
</body>
</html>