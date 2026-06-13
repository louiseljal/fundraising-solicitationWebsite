<?php require_once 'api/admin_protect.php'; ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Activity Logs</title>
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
                <a class="nav-link text-start" href="membership.php">
                    <i class="bi bi-people-fill me-2"></i> User Membership
                </a>
                <a class="nav-link active text-start" href="activity_log.php">
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
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--text-color);">System Activity Logs</h4>
                    <p class="text-muted small mb-0">Chronological database transaction history audit trail.</p>
                </div>
            </header>

            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filter-start-date" class="form-label small fw-bold text-muted">Start Date</label>
                        <input type="date" class="form-control rounded-3" id="filter-start-date" onchange="clearPeriodPreset()">
                    </div>
                    <div class="col-md-3">
                        <label for="filter-end-date" class="form-label small fw-bold text-muted">End Date</label>
                        <input type="date" class="form-control rounded-3" id="filter-end-date" onchange="clearPeriodPreset()">
                    </div>
                    <div class="col-md-3">
                        <label for="filter-period" class="form-label small fw-bold text-muted">Timeframe Preset</label>
                        <select class="form-select rounded-3" id="filter-period" onchange="applyPeriodPreset()">
                            <option value="all" selected>Custom Range</option>
                            <option value="daily">Today (Daily)</option>
                            <option value="monthly">This Month (Monthly)</option>
                            <option value="yearly">This Year (Yearly)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-activity-type" class="form-label small fw-bold text-muted">Table / Module Filter</label>
                        <select class="form-select rounded-3" id="filter-activity-type">
                            <option value="all">All Available Modules</option>
                            <option value="Donation">Donations</option>
                            <option value="Campaign">Campaigns</option>
                            <option value="User">Users</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-success rounded-3 w-100 fw-semibold" onclick="loadLogs()">
                        <i class="bi bi-funnel-fill me-1"></i> Apply Filter
                    </button>
                    <button class="btn btn-outline-secondary rounded-3 fw-semibold px-3" onclick="clearFilters()">
                        Reset
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-transparent border-0 p-3 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-ul me-2"></i> Activity Stream</h6>
                    <span class="badge bg-light text-dark border" id="log-count">0 Records Found</span>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase fs-7 text-muted" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="ps-4" style="width: 20%;">Timestamp</th>
                                    <th style="width: 20%;">Activity Type</th>
                                    <th class="pe-4" style="width: 60%;">Record Details</th>
                                </tr>
                            </thead>
                            <tbody id="logs-tbody">
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div> Loading activity stream...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function getBadgeClass(type) {
            switch(type) {
                case 'Donation': return 'bg-success bg-opacity-10 text-success border-success';
                case 'Campaign': return 'bg-primary bg-opacity-10 text-primary border-primary';
                case 'User': return 'bg-info bg-opacity-10 text-info border-info';
                default: return 'bg-secondary bg-opacity-10 text-secondary border-secondary';
            }
        }

        function formatDate(dStr) {
            if (!dStr) return '---';
            const date = new Date(dStr);
            return date.toLocaleString('en-US', { 
                year: 'numeric', month: 'short', day: 'numeric', 
                hour: '2-digit', minute: '2-digit', second: '2-digit' 
            });
        }

        function applyPeriodPreset() {
            const period = document.getElementById('filter-period').value;
            if (period !== 'all') {
                // Clear manual inputs when a dropdown preset is chosen
                document.getElementById('filter-start-date').value = '';
                document.getElementById('filter-end-date').value = '';
                loadLogs();
            }
        }

        function clearPeriodPreset() {
            document.getElementById('filter-period').value = 'all';
        }

        function loadLogs() {
            const startDate = document.getElementById('filter-start-date').value;
            const endDate = document.getElementById('filter-end-date').value;
            const period = document.getElementById('filter-period').value;
            const activityType = document.getElementById('filter-activity-type').value;

            let url = `api/activity_logs.php?t=${Date.now()}`;
            
            if (period !== 'all') {
                url += `&period=${period}`;
            } else if (startDate && endDate) {
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }
            
            if (activityType && activityType !== 'all') {
                url += `&activity_type=${activityType}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('logs-tbody');
                    if (!data.success) {
                        tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-danger">Failed loading activity logs.</td></tr>`;
                        return;
                    }

                    const logs = data.logs || [];
                    document.getElementById('log-count').innerText = `${logs.length} Records Found`;

                    if (logs.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">No activities found within the selected criteria.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = logs.map(log => `
                        <tr>
                            <td class="ps-4 fw-semibold text-muted fs-7" style="white-space: nowrap;">${formatDate(log.created_at)}</td>
                            <td>
                                <span class="badge border rounded-pill px-2 py-1 fw-semibold ${getBadgeClass(log.activity_type)}">
                                    ${log.activity_type}
                                </span>
                            </td>
                            <td class="pe-4 font-monospace small text-dark">${log.details}</td>
                        </tr>
                    `).join('');
                })
                .catch(err => {
                    console.error("Error loading logs:", err);
                    document.getElementById('logs-tbody').innerHTML = `<tr><td colspan="3" class="text-center py-4 text-danger">Network or system error occurred.</td></tr>`;
                });
        }

        function clearFilters() {
            document.getElementById('filter-start-date').value = '';
            document.getElementById('filter-end-date').value = '';
            document.getElementById('filter-period').value = 'all';
            document.getElementById('filter-activity-type').value = 'all';
            loadLogs();
        }

        document.addEventListener("DOMContentLoaded", loadLogs);
    </script>

    <script src="main.js"></script>
</body>
</html>