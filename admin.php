<?php require_once 'api/admin_protect.php'; ?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quick Overview</title>
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
                <a class="nav-link active text-start" href="admin.php">
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
            
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--text-color);">System Overview</h4>
                    <p class="text-muted small mb-0">High-level platform pulses and summary metrics.</p>
                </div>
                
                <div class="user-profile d-flex align-items-center bg-white p-1 pe-3 rounded-pill shadow-sm">
                    <div class="user-avatar text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="background-color: var(--sidebar-green); width: 32px; height: 32px;">A</div>
                    <div>
                        <div class="fw-bold lh-1 small" style="color: var(--text-color);">System Admin</div>
                        <small class="text-muted text-uppercase user-role-text" style="font-size: 0.65rem;">Workspace Master</small>
                    </div>
                </div>
            </header>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 p-4 bg-success-subtle shadow-sm h-100">
                        <div class="d-flex align-items-center">
                            <div class="app-icon-badge bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; border-radius: 12px;"><i class="bi bi-wallet2 fs-5"></i></div>
                            <div>
                                <h6 class="text-success mb-1 fw-bold small text-uppercase" style="letter-spacing: 0.03em;">Total Raised</h6>
                                <h3 class="mb-0 fw-bolder text-dark" id="kpi-totalFunds">₱0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 p-4 bg-primary-subtle shadow-sm h-100">
                        <div class="d-flex align-items-center">
                            <div class="app-icon-badge bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; border-radius: 12px;"><i class="bi bi-people-fill fs-5"></i></div>
                            <div>
                                <h6 class="text-primary mb-1 fw-bold small text-uppercase" style="letter-spacing: 0.03em;">Unique Donors</h6>
                                <h3 class="mb-0 fw-bolder text-dark" id="kpi-totalDonors">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 p-4 bg-warning-subtle shadow-sm h-100">
                        <div class="d-flex align-items-center">
                            <div class="app-icon-badge bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; border-radius: 12px;"><i class="bi bi-hourglass-split fs-5"></i></div>
                            <div>
                                <h6 class="text-warning-emphasis mb-1 fw-bold small text-uppercase" style="letter-spacing: 0.03em;">Pending Actions</h6>
                                <h3 class="mb-0 fw-bolder text-dark" id="kpi-pendingActions">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 p-4 bg-info-subtle shadow-sm h-100">
                        <div class="d-flex align-items-center">
                            <div class="app-icon-badge bg-info text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; border-radius: 12px;"><i class="bi bi-megaphone-fill fs-5"></i></div>
                            <div>
                                <h6 class="text-info-emphasis mb-1 fw-bold small text-uppercase" style="letter-spacing: 0.03em;">Active Campaigns</h6>
                                <h3 class="mb-0 fw-bolder text-dark" id="kpi-activeCampaigns">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card border-0 rounded-4 p-4 shadow-sm bg-white">
                <div class="row align-items-center py-2">
                    <div class="col-lg-7">
                        <h5 class="fw-bold text-dark mb-2">Hello, Workspace Master! 👋</h5>
                        <p class="text-muted small mb-3">All systems are running tracking nominally. This landing area acts purely as a data pulse viewpoint. To modify active operational elements, manage campaign structures, or approve pending clearance queues, utilize the newly updated panels options on the sidebar.</p>
                        <div class="d-flex gap-2">
                            <a href="manage_campaigns.php" class="btn btn-brand btn-sm rounded-pill text-white px-3 fw-semibold"><i class="bi bi-plus-circle-fill me-1"></i> Go to Campaigns</a>
                            <a href="analytics.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold"><i class="bi bi-graph-up-arrow me-1"></i> View Trends</a>
                        </div>
                    </div>
                    <div class="col-lg-5 d-none d-lg-block text-center">
                        <i class="bi bi-activity text-brand-color display-1 opacity-25"></i>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const formatCurrency = (val) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);

        function loadOverviewMetrics() {
            fetch('api/admin_backend.php')
                .then(response => response.json())
                .then(data => {
                    if(data.error) return console.error(data.error);

                    document.getElementById('kpi-totalFunds').innerText = formatCurrency(data.metrics.totalFundsRaised);
                    document.getElementById('kpi-totalDonors').innerText = data.metrics.totalUniqueDonors.toLocaleString();
                    
                    const pendingActions = data.pendingTransactions.length + data.pendingSolicitations.length;
                    document.getElementById('kpi-pendingActions').innerText = pendingActions.toLocaleString();
                    
                    const activeCamps = data.campaigns.filter(c => c.campaign_status === 'Active').length;
                    document.getElementById('kpi-activeCampaigns').innerText = activeCamps.toLocaleString();
                }).catch(err => console.error("Metrics load failure:", err));
        }

        document.addEventListener("DOMContentLoaded", loadOverviewMetrics);
    </script>

    <script src="main.js"></script>
</body>
</html>