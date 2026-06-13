<?php require_once 'api/admin_protect.php'; ?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Performance Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a class="nav-link active text-start" href="analytics.php">
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
                
            </nav>
            
            <div class="sidebar-footer pt-3 border-top border-secondary-subtle">
                <span class="text-uppercase text-white-50 small px-2 mb-1 d-block sidebar-section-header">Settings</span>
                
                <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                
                
            </div>
        </aside>

        <main class="flex-grow-1 p-4" style="background-color: var(--bg-main); height: 100vh; overflow-y: auto;">
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--text-color);">Performance Analytics</h4>
                    <p class="text-muted small mb-0">Financial trends and historical matrix charting tools.</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success rounded-3 px-3" onclick="exportCsvReport()">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                    </button>
                    <button class="btn btn-danger rounded-3 px-3" onclick="exportPdfReport()">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </button>
                </div>
            </header>

            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filter-start-date" class="form-label small fw-bold text-muted">Start Date</label>
                        <input type="date" class="form-control rounded-3" id="filter-start-date">
                    </div>
                    <div class="col-md-3">
                        <label for="filter-end-date" class="form-label small fw-bold text-muted">End Date</label>
                        <input type="date" class="form-control rounded-3" id="filter-end-date">
                    </div>
                    <div class="col-md-3">
                        <label for="filter-group-by" class="form-label small fw-bold text-muted">View/Group Trends By</label>
                        <select class="form-select rounded-3" id="filter-group-by">
                            <option value="daily">Daily</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-success rounded-3 w-100 fw-semibold" onclick="loadAnalytics()">
                            <i class="bi bi-funnel-fill me-1"></i> Apply Filter
                        </button>
                        <button class="btn btn-outline-secondary rounded-3 fw-semibold px-3" onclick="clearFilters()">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">Gross Total Raised</span>
                                <h4 class="fw-bold mb-0 text-dark" id="kpi-totalRaised">₱0.00</h4>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">Unique Donors</span>
                                <h4 class="fw-bold mb-0 text-dark" id="kpi-uniqueDonors">0</h4>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">Active Members</span>
                                <h4 class="fw-bold mb-0 text-dark" id="kpi-activeMembers">0</h4>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-check-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">Total Registered (Filtered)</span>
                                <h4 class="fw-bold mb-0 text-dark" id="kpi-monthlyMembers">0</h4>
                            </div>
                            <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-plus-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">Total Collections</span>
                                <h4 class="fw-bold mb-0 text-dark" id="kpi-totalCollections">0</h4>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-grid fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">Total Solicitations</span>
                                <h4 class="fw-bold mb-0 text-dark" id="kpi-totalSolicitations">0</h4>
                            </div>
                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-inboxes-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="stat-card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-graph-up text-primary me-2"></i> Gross Donation Trends (<span id="trend-label-mode">Monthly</span>)</h6>
                        <div style="position: relative; height:320px; width:100%">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="stat-card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-bar-chart-fill text-success me-2"></i> Top 5 Fund Solicitors</h6>
                        <div style="position: relative; height:320px; width:100%">
                            <canvas id="solicitorsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="stat-card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-wallet2 text-info me-2"></i> Payment Method Distribution</h6>
                        <div style="position: relative; height:320px; width:100%">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="stat-card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-person-plus-fill text-warning me-2"></i> New Registered Users (<span id="user-trend-label-mode">Monthly</span>)</h6>
                        <div style="position: relative; height:320px; width:100%">
                            <canvas id="userRegistrationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="stat-card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> Completed Campaigns Trend (<span id="comp-trend-label-mode">Monthly</span>)</h6>
                        <div style="position: relative; height:360px; width:100%">
                            <canvas id="completedCampaignsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const formatCurrency = (val) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);

        // Store Chart instances globally to destroy them on refresh
        let trendChartObj, solicitorsChartObj, paymentMethodChartObj, userRegistrationChartObj, completedCampaignsChartObj;

        function loadAnalytics() {
            const startDate = document.getElementById('filter-start-date').value;
            const endDate = document.getElementById('filter-end-date').value;
            const groupBy = document.getElementById('filter-group-by').value;

            // Dynamically update axis text depending on select option
            const groupText = document.getElementById('filter-group-by').options[document.getElementById('filter-group-by').selectedIndex].text;
            document.getElementById('trend-label-mode').innerText = groupText;
            document.getElementById('user-trend-label-mode').innerText = groupText;
            document.getElementById('comp-trend-label-mode').innerText = groupText;

            let url = `api/analytics_backend.php?action=dashboard&group_by=${groupBy}`;
            if (startDate && endDate) {
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if(data.error) return alert(data.error);
                    if(!data.success) return;

                    // Update Top KPI Blocks
                    if (data.kpis) {
                        document.getElementById('kpi-totalRaised').innerText = formatCurrency(data.kpis.total_raised || 0);
                        document.getElementById('kpi-uniqueDonors').innerText = parseInt(data.kpis.unique_donors || 0).toLocaleString();
                    }
                    document.getElementById('kpi-activeMembers').innerText = parseInt(data.activeMembers || 0).toLocaleString();
                    document.getElementById('kpi-monthlyMembers').innerText = parseInt(data.monthlyMembers || 0).toLocaleString();
                    
                    // Update Additional Data KPI Blocks
                    if (data.additionalData) {
                        document.getElementById('kpi-totalCollections').innerText = parseInt(data.additionalData.totalCollections || 0).toLocaleString();
                        document.getElementById('kpi-totalSolicitations').innerText = parseInt(data.additionalData.totalSolicitations || 0).toLocaleString();
                    }

                    // Destroy old charts to prevent overlapping/canvas rendering bugs
                    if (trendChartObj) trendChartObj.destroy();
                    if (solicitorsChartObj) solicitorsChartObj.destroy();
                    if (paymentMethodChartObj) paymentMethodChartObj.destroy();
                    if (userRegistrationChartObj) userRegistrationChartObj.destroy();
                    if (completedCampaignsChartObj) completedCampaignsChartObj.destroy();

                    // Line Chart 1: Donation Trends
                    const trendCtx = document.getElementById('trendChart').getContext('2d');
                    trendChartObj = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: data.charts.trendLabels,
                            datasets: [{
                                label: `Accumulated Volume (${groupBy.toUpperCase()})`,
                                data: data.charts.trendValues,
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22, 163, 74, 0.08)',
                                fill: true, tension: 0.3, borderWidth: 2.5
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });

                    // Bar Chart 2: Top Solicitors
                    const solicitorsCtx = document.getElementById('solicitorsChart').getContext('2d');
                    solicitorsChartObj = new Chart(solicitorsCtx, {
                        type: 'bar',
                        data: {
                            labels: data.topSolicitors.map(s => s.full_name),
                            datasets: [{
                                label: 'Funds Raised (₱)',
                                data: data.topSolicitors.map(s => parseFloat(s.total_funds)),
                                backgroundColor: ['#3a86ff', '#10b981', '#ffc107', '#dc3545', '#6c757d'],
                                borderRadius: 6
                            }]
                        },
                        options: { 
                            responsive: true, maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } },
                            plugins: { legend: { display: false } }
                        }
                    });

                    // Doughnut Chart 3: Payment Method Breakdown
                    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
                    const pmData = data.additionalData.paymentMethodBreakdown || [];
                    paymentMethodChartObj = new Chart(paymentMethodCtx, {
                        type: 'doughnut',
                        data: {
                            labels: pmData.map(p => p.payment_method ? p.payment_method.replace('_', ' ') : 'Unknown'),
                            datasets: [{
                                label: 'Amount (₱)',
                                data: pmData.map(p => parseFloat(p.total_amount) || 0),
                                backgroundColor: ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
                                hoverOffset: 4
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false 
                        }
                    });

                    // Line Chart 4: New Registered Users
                    const userRegistrationCtx = document.getElementById('userRegistrationChart').getContext('2d');
                    userRegistrationChartObj = new Chart(userRegistrationCtx, {
                        type: 'line',
                        data: {
                            labels: data.charts.userTrendLabels,
                            datasets: [{
                                label: `New Registrations (${groupBy.toUpperCase()})`,
                                data: data.charts.userTrendValues,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.08)',
                                fill: true, tension: 0.3, borderWidth: 2.5
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false 
                        }
                    });

                    // Line Chart 5: Completed Campaigns Trend
                    const compCampCtx = document.getElementById('completedCampaignsChart').getContext('2d');
                    completedCampaignsChartObj = new Chart(compCampCtx, {
                        type: 'line',
                        data: {
                            labels: data.charts.completedTrendLabels,
                            datasets: [{
                                label: `Completed Campaigns (${groupBy.toUpperCase()})`,
                                data: data.charts.completedTrendValues,
                                borderColor: '#8b5cf6', // Distinct purple color
                                backgroundColor: 'rgba(139, 92, 246, 0.08)',
                                fill: true, tension: 0.3, borderWidth: 2.5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { 
                                y: { 
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 } // Ensures whole numbers (you can't have 1.5 campaigns)
                                } 
                            }
                        }
                    });

                }).catch(err => console.error("Error loading analytics:", err));
        }

        function exportCsvReport() {
            const startDate = document.getElementById('filter-start-date').value;
            const endDate = document.getElementById('filter-end-date').value;
            
            let url = 'api/export_analytics_csv.php?';
            if (startDate && endDate) {
                url += `start_date=${startDate}&end_date=${endDate}`;
            }
            window.open(url, '_blank');
        }

        function exportPdfReport() {
            const startDate = document.getElementById('filter-start-date').value;
            const endDate = document.getElementById('filter-end-date').value;
            
            let url = 'api/export_analytics.php?';
            if (startDate && endDate) {
                url += `start_date=${startDate}&end_date=${endDate}`;
            }
            window.open(url, '_blank');
        }

        function clearFilters() {
            document.getElementById('filter-start-date').value = '';
            document.getElementById('filter-end-date').value = '';
            document.getElementById('filter-group-by').value = 'monthly';
            loadAnalytics();
        }

        document.addEventListener("DOMContentLoaded", loadAnalytics);
    </script>

    <script src="main.js"></script>
</body>
</html>