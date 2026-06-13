<?php require_once 'api/admin_protect.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Management - Membership</title>
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
                <a class="nav-link text-start" href="queues.php">
                    <i class="bi bi-inboxes-fill me-2"></i> Verification Queues
                </a>
                <a class="nav-link active text-start" href="membership.php">
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

        <main class="main-content flex-grow-1 p-4">
            
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div class="search-bar position-relative w-50">
                    
                    
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light rounded-circle text-muted position-relative">
                        <i class="bi bi-bell"></i>
                    </button>
                    <button class="btn btn-light rounded-circle text-muted position-relative me-2">
                        <i class="bi bi-envelope"></i>
                    </button>
                    
                    <div class="user-profile d-flex align-items-center bg-light p-1 pe-3 rounded-pill">
                        <div id="user-avatar-initial" class="user-avatar text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="background-color: #16a34a;">A</div>
                        <div>
                            <div id="user-display-name" class="fw-bold lh-1 small text-dark">System Admin</div>
                            <small id="user-display-role" class="text-muted text-uppercase user-role-text">Master Core</small>
                        </div>
                    </div>
                </div>
            </header>

            <ul class="nav nav-tabs-custom gap-3 mb-4 border-bottom" id="membershipTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-overview-btn" href="#tab-overview">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-members-btn" href="#tab-members">Members</a>
                </li>
            </ul>

            <div class="tab-content">
                
                <div class="tab-pane fade show active" id="pane-overview">
                    <section class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
                        <div class="col">
                            <div class="card stat-card p-3 border-0 rounded-3 shadow-sm bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon rounded-circle bg-primary-subtle text-primary p-2 fs-4"><i class="bi bi-people-fill"></i></div>
                                    <div>
                                        <small class="text-muted d-block stat-label">Total Members</small>
                                        <h4 id="total-members-count" class="fw-bold mb-0">--</h4>
                                    </div>
                                    </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card stat-card p-3 border-0 rounded-3 shadow-sm bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon rounded-circle bg-success-subtle text-success p-2 fs-4"><i class="bi bi-person-check-fill"></i></div>
                                    <div>
                                        <small class="text-muted d-block stat-label">Active Accounts</small>
                                        <h4 id="active-members-count" class="fw-bold mb-0">--</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card stat-card p-3 border-0 rounded-3 shadow-sm bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon rounded-circle bg-warning-subtle text-warning p-2 fs-4"><i class="bi bi-collection-fill"></i></div>
                                    <div>
                                        <small class="text-muted d-block stat-label">Total Campaigns</small>
                                        <h4 id="active-collections-count" class="fw-bold mb-0">--</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card stat-card p-3 border-0 rounded-3 shadow-sm bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon rounded-circle bg-danger-subtle text-danger p-2 fs-4"><i class="bi bi-cash-stack"></i></div>
                                    <div>
                                        <small class="text-muted d-block stat-label">Total Funds Raised</small>
                                        <h4 id="total-funds-raised" class="fw-bold mb-0">--</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="row g-4">
                        <div class="col-xl-6 col-lg-12">
                            <div class="card data-card border-0 p-4 rounded-4 h-100 shadow-sm bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h6 class="fw-bold text-dark mb-0">System Summary Notice</h6>
                                    <button class="btn btn-brand btn-sm rounded-pill px-3 py-1" onclick="location.reload()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh Data
                                    </button>
                                </div>
                                
                                <div class="chart-container p-4 d-flex flex-column align-items-center justify-content-center bg-light rounded-3 text-center" id="member-growth-chart-wrapper" style="min-height: 250px;">
                                    <i class="bi bi-shield-check text-success display-4 mb-2"></i>
                                    <h5>Data Validation Node Secure</h5>
                                    <p class="text-muted small max-width-300">Live operational loops pulling relational records from schema tables are fully initialized.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card data-card border-0 p-4 rounded-4 h-100 d-flex flex-column shadow-sm bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-dark mb-0">Recent Members</h6>
                                    <a href="#tab-members" class="text-success small fw-bold text-decoration-none trigger-view-members">View All</a>
                                </div>
                                
                                <div class="d-flex flex-column gap-3 mb-3" id="recent-members-list-container">
                                    <div class="text-center text-muted small py-4">Loading members...</div>
                                </div>
                                
                                <button class="btn btn-brand mt-auto w-100 rounded-pill py-2 btn-sm trigger-view-members">
                                    View All Members &rarr;
                                </button>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card data-card border-0 p-4 rounded-4 h-100 d-flex flex-column shadow-sm bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-dark mb-0">Administrators</h6>
                                    <a href="#tab-members" class="text-success small fw-bold text-decoration-none trigger-view-members">View All</a>
                                </div>
                                
                                <div class="d-flex flex-column gap-3 mb-3" id="administrators-list-container">
                                    <div class="text-center text-muted small py-4">Loading administrators...</div>
                                </div>
                                
                                <button class="btn btn-brand mt-auto w-100 rounded-pill py-2 btn-sm trigger-view-members">
                                    View Admin Access &rarr;
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="tab-pane fade" id="pane-members">
                    <div class="card data-card border-0 p-4 rounded-4 shadow-sm bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Account Directory Index</h5>
                                <p class="text-muted small mb-0">Full systemic log listing of active registered database nodes.</p>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btn-back-to-overview">
                                &larr; Back to Overview
                            </button>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" id="directory-search-bar" class="form-control bg-light border-0" placeholder="Quick search by username, name, or email..." oninput="executeDirectorySearch()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="directory-role-filter" class="form-select bg-light border-0 py-2 fw-semibold text-muted" onchange="executeDirectorySearch()">
                                    <option value="all" selected>All Types (Admin & Donor)</option>
                                    <option value="Admin">Admin System Roles</option>
                                    <option value="Donor">Donor Community Roles</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive rounded-3 border" style="max-height: 480px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 position-relative">
                                <thead class="table-light small text-uppercase text-secondary position-sticky top-0 style-sticky-head" style="z-index: 5; background-color: #f8f9fa;">
                                    <tr>
                                        <th>User ID</th>
                                        <th>Account Profile</th>
                                        <th>Email</th>
                                        <th>Security Authorization</th>
                                        <th>Account Status</th>
                                        <th>System Entry Stamp</th>
                                    </tr>
                                </thead>
                                <tbody id="full-directory-table-body" class="small">
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No systemic records loaded.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill text-warning me-2 fs-4"></i> Confirm Admin Identity
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetPendingAction()"></button>
                </div>
                <div class="modal-body px-4">
                    <p class="text-muted small mb-3">Modifying core user roles or account statuses is a highly privileged action. Please enter your administrator password to proceed.</p>
                    <div class="mb-2">
                        <label for="admin-confirm-password" class="form-label small fw-bold text-secondary">Your Admin Password</label>
                        <input type="password" class="form-control rounded-3" id="admin-confirm-password" placeholder="Enter password to authorize">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal" onclick="resetPendingAction()">Cancel Action</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" onclick="verifyAndExecute()">Verify & Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let cachedMasterUserList = [];
        let pendingUpdate = null;

        document.addEventListener("DOMContentLoaded", function() {
            const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);

            // 1. DATA COLLECTION ROUTER ENGINE
            function fetchMemberData() {
                fetch('api/members.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        // Map KPI Cards
                        document.getElementById('total-members-count').innerText = data.metrics.totalMembers.toLocaleString();
                        document.getElementById('active-members-count').innerText = data.metrics.activeMembers.toLocaleString();
                        document.getElementById('active-collections-count').innerText = data.metrics.activeCollections.toLocaleString();
                        document.getElementById('total-funds-raised').innerText = formatMoney(data.metrics.totalFundsRaised);

                        // Loop and Map Recent Members UI List
                        const recentContainer = document.getElementById('recent-members-list-container');
                        recentContainer.innerHTML = '';
                        
                        if (data.recentMembers.length === 0) {
                            recentContainer.innerHTML = '<div class="text-center text-muted small py-3">No standard members found.</div>';
                        } else {
                            data.recentMembers.slice(0, 4).forEach(member => {
                                const item = document.createElement('div');
                                item.className = 'd-flex align-items-center gap-2 border-bottom pb-2';
                                item.innerHTML = `
                                    <div class="rounded-circle text-white fw-bold bg-secondary d-flex align-items-center justify-content-center text-uppercase" style="width: 32px; height: 32px; font-size: 13px;">
                                        ${member.username.charAt(0)}
                                    </div>
                                    <div class="lh-sm">
                                        <div class="fw-bold small text-dark">${escapeHTML(member.username)}</div>
                                        <small class="text-muted" style="font-size: 11px;">Joined ${member.joined_date}</small>
                                    </div>
                                `;
                                recentContainer.appendChild(item);
                            });
                        }

                        // Loop and Map Administrators UI List
                        const adminContainer = document.getElementById('administrators-list-container');
                        adminContainer.innerHTML = '';

                        if (data.admins.length === 0) {
                            adminContainer.innerHTML = '<div class="text-center text-muted small py-3">No administrators logged.</div>';
                        } else {
                            data.admins.forEach(admin => {
                                const item = document.createElement('div');
                                item.className = 'd-flex align-items-center gap-2 border-bottom pb-2';
                                item.innerHTML = `
                                    <div class="rounded-circle text-white fw-bold bg-success d-flex align-items-center justify-content-center text-uppercase" style="width: 32px; height: 32px; font-size: 13px;">
                                        ${admin.username.charAt(0)}
                                    </div>
                                    <div class="lh-sm">
                                        <div class="fw-bold small text-dark">${escapeHTML(admin.username)}</div>
                                        <small class="badge bg-success-subtle text-success p-1" style="font-size: 9px;">Admin</small>
                                    </div>
                                `;
                                adminContainer.appendChild(item);
                            });
                        }

                        // Combine and deduplicate users for the master directory
const combinedList = [...data.admins, ...data.recentMembers];
const uniqueUsersMap = new Map();

// Use uniqueUsersMap here
combinedList.forEach(u => uniqueUsersMap.set(u.user_id, u));

// Use uniqueUsersMap here too
cachedMasterUserList = Array.from(uniqueUsersMap.values());
                        
                        // Re-apply search/filter
                        executeDirectorySearch();
                    })
                    .catch(err => console.error("Error linking with membership dashboard core:", err));
            }

            fetchMemberData();
            setInterval(fetchMemberData, 5000);

            // 2. VIEW ALL TAB NAVIGATION SWITCH LOGIC CONTROLLER
            const overviewBtn = document.getElementById('tab-overview-btn');
            const membersBtn = document.getElementById('tab-members-btn');
            const paneOverview = document.getElementById('pane-overview');
            const paneMembers = document.getElementById('pane-members');

            function switchToMembersTab() {
                overviewBtn.classList.remove('active');
                membersBtn.classList.add('active');
                paneOverview.classList.remove('show', 'active');
                paneMembers.classList.add('show', 'active');
            }

            function switchToOverviewTab() {
                membersBtn.classList.remove('active');
                overviewBtn.classList.add('active');
                paneMembers.classList.remove('show', 'active');
                paneOverview.classList.add('show', 'active');
            }

            overviewBtn.addEventListener('click', function(e) { e.preventDefault(); switchToOverviewTab(); });
            membersBtn.addEventListener('click', function(e) { e.preventDefault(); switchToMembersTab(); });
            document.getElementById('btn-back-to-overview').addEventListener('click', switchToOverviewTab);

            document.querySelectorAll('.trigger-view-members').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    switchToMembersTab();
                });
            });
        });

        // 3. DIRECTORY TABLE ENGINE
        function buildDirectoryTable(users) {
            const tableBody = document.getElementById('full-directory-table-body');
            tableBody.innerHTML = '';

            if (users.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-muted">No registered members found matching criteria.</td></tr>`;
                return;
            }

            users.sort((a, b) => a.user_id - b.user_id);

            users.forEach(user => {
                const tr = document.createElement('tr');
                
                const userRole = ((user.user_role || user.role) || '').toLowerCase() === 'admin' ? 'Admin' : 'Donor';
                const isSystemAdmin = (userRole === 'Admin');
                const joinedDate = user.joined_date ? user.joined_date : 'N/A';
                const initialBg = isSystemAdmin ? 'bg-success' : 'bg-secondary';
                const fullName = (user.first_name && user.last_name) ? `${escapeHTML(user.first_name)} ${escapeHTML(user.last_name)}` : 'N/A';
                const emailVal = user.email ? escapeHTML(user.email) : 'N/A';
                const accountStatus = user.account_status || 'Active';

                const roleSelect = `
                    <select class="form-select form-select-sm border-0 bg-light fw-bold" data-previous="${userRole}" onchange="handleUpdateRole(${user.user_id}, this.value, this)">
                        <option value="Admin" ${userRole === 'Admin' ? 'selected' : ''}>Admin</option>
                        <option value="Donor" ${userRole === 'Donor' ? 'selected' : ''}>Donor</option>
                    </select>
                `;

                const statusSelect = `
                    <select class="form-select form-select-sm border-0 bg-light fw-bold ${accountStatus === 'Suspended' ? 'text-danger' : 'text-success'}" data-previous="${accountStatus}" onchange="handleUpdateStatus(${user.user_id}, this.value, this)">
                        <option value="Active" ${accountStatus === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Suspended" ${accountStatus === 'Suspended' ? 'selected' : ''}>Suspended</option>
                    </select>
                `;

                tr.innerHTML = `
                    <td class="text-muted font-monospace">#${user.user_id}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center text-uppercase ${initialBg}" style="width: 28px; height: 28px; font-size: 11px;">
                                ${user.username.charAt(0)}
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block">${escapeHTML(user.username)}</span>
                                <small class="text-muted" style="font-size: 10px;">${fullName}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small">${emailVal}</td>
                    <td>${roleSelect}</td>
                    <td style="width: 130px;">${statusSelect}</td>
                    <td class="text-muted">${joinedDate}</td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // 4. QUICK SEARCH & FILTERING LOGIC
        function executeDirectorySearch() {
            const query = document.getElementById('directory-search-bar').value.toLowerCase().trim();
            const roleFilter = document.getElementById('directory-role-filter').value;
            
            const filteredResults = cachedMasterUserList.filter(user => {
                const username = (user.username || '').toLowerCase();
                const email = (user.email || '').toLowerCase();
                const firstName = (user.first_name || '').toLowerCase();
                const lastName = (user.last_name || '').toLowerCase();
                const userRole = ((user.user_role || user.role) || '');

                const matchesQuery = !query || 
                                     username.includes(query) || 
                                     email.includes(query) || 
                                     firstName.includes(query) || 
                                     lastName.includes(query);

                const matchesRole = roleFilter === 'all' || userRole === roleFilter;

                return matchesQuery && matchesRole;
            });

            buildDirectoryTable(filteredResults);
        }

        // 5. IN-LINE EDITING EVENT HANDLERS (TRIGGERS MODAL POPUP)
        function handleUpdateRole(userId, newRole, selectElement) {
            pendingUpdate = { 
                userId, 
                roleVal: newRole, 
                statusVal: null, 
                selectElement, 
                previousValue: selectElement.dataset.previous 
            };
            
            const modal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
            modal.show();
        }

        function handleUpdateStatus(userId, newStatus, selectElement) {
            pendingUpdate = { 
                userId, 
                roleVal: null, 
                statusVal: newStatus, 
                selectElement, 
                previousValue: selectElement.dataset.previous 
            };
            
            const modal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
            modal.show();
        }

        // Triggered upon confirming admin password inside the modal
        function verifyAndExecute() {
            const password = document.getElementById('admin-confirm-password').value;
            if (!password.trim()) {
                alert("Administrator password is required to save changes.");
                return;
            }

            if (pendingUpdate) {
                sendApiUpdateRequest(
                    pendingUpdate.userId, 
                    pendingUpdate.statusVal, 
                    pendingUpdate.roleVal, 
                    password, 
                    pendingUpdate.selectElement
                );
            }
        }

        // Dispatches authorized update request to API endpoint
        function sendApiUpdateRequest(userId, statusVal, roleVal, adminPassword, selectElement) {
            const payload = new FormData();
            payload.append('user_id', userId);
            payload.append('admin_password', adminPassword);
            if (statusVal) payload.append('account_status', statusVal);
            if (roleVal) payload.append('user_role', roleVal);

            fetch('api/members.php', {
                method: 'POST',
                body: payload
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('passwordConfirmModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    
                    if (selectElement) {
                        selectElement.dataset.previous = selectElement.value;
                        if (statusVal) {
                            selectElement.className = `form-select form-select-sm border-0 bg-light fw-bold ${statusVal === 'Suspended' ? 'text-danger' : 'text-success'}`;
                        }
                    }
                    
                    alert("Account updated securely!");
                    resetPendingAction();
                } else {
                    alert("Security verification error: " + (data.error || 'Unauthorized action refused.'));
                    resetPendingAction(true); // Reverts dropdown selection safely
                }
            })
            .catch(err => {
                console.error("Communication disruption with endpoint:", err);
                alert("Network communication issue encountered.");
                resetPendingAction(true);
            });
        }

        // Resets the modal and handles dropdown reverting if rejected
        function resetPendingAction(revertSelection = false) {
            if (revertSelection && pendingUpdate && pendingUpdate.selectElement) {
                pendingUpdate.selectElement.value = pendingUpdate.previousValue;
            }
            pendingUpdate = null;
            document.getElementById('admin-confirm-password').value = '';
            
            // Clean up backdrop/modal instance
            let backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }

        function escapeHTML(str) {
            return str ? str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
        }
    </script>

    <script src="main.js"></script>
</body>
</html>