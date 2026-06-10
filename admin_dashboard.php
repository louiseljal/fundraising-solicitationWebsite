<?php
// admin-dashboard.php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';

// Force authentication and verify admin status before showing the page
if (!isset($_SESSION['user_id']) || !isAdminRole($_SESSION['user_role'] ?? '')) {
    // Not an admin? Kick them back to the login page
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <aside class="sidebar p-3 text-white">
            <div class="sidebar-logo d-flex align-items-center mb-4 px-2">
                <i class="bi bi-shield-lock-fill me-2 fs-4 text-warning"></i>
                <span class="fw-bold lh-sm">Admin Portal <br><small class="fw-normal text-white-50">Management Node</small></span>
            </div>
            <nav class="nav flex-column gap-1 flex-grow-1">
                <span class="text-uppercase text-white-50 small px-2 mb-1 sidebar-section-header">Core Operations</span>
                <a class="nav-link active" href="admin-dashboard.html"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a class="nav-link" href="solicitations.html"><i class="bi bi-megaphone-fill me-2"></i> Public View</a>
            </nav>
        </aside>

        <main class="main-content flex-grow-1 p-4 main-workspace-bg">
            <div class="container-fluid">
                <div class="mb-4">
                    <h4 class="fw-bold mb-1">Operational Overview</h4>
                    <p class="text-muted small">Moderate solicitation request queues and track community funding metrics.</p>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 p-3 rounded-4 shadow-sm bg-white">
                            <div class="d-flex align-items-center">
                                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 me-3"><i class="bi bi-list-stars fs-4"></i></div>
                                <div>
                                    <h6 class="text-muted small mb-1">Total Solicitations</h6>
                                    <h3 class="fw-bold mb-0" id="metric-total">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 p-3 rounded-4 shadow-sm bg-white">
                            <div class="d-flex align-items-center">
                                <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3 me-3"><i class="bi bi-clock-history fs-4"></i></div>
                                <div>
                                    <h6 class="text-muted small mb-1">Pending Approval</h6>
                                    <h3 class="fw-bold mb-0" id="metric-pending">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 p-3 rounded-4 shadow-sm bg-white">
                            <div class="d-flex align-items-center">
                                <div class="p-3 bg-success bg-opacity-10 text-success rounded-3 me-3"><i class="bi bi-check2-circle fs-4"></i></div>
                                <div>
                                    <h6 class="text-muted small mb-1">Active/Completed</h6>
                                    <h3 class="fw-bold mb-0" id="metric-approved">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 p-4 rounded-4 shadow-sm bg-white">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-check me-2 text-success"></i>Solicitation Approval Queue</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Submitted By</th>
                                    <th>Campaign Details</th>
                                    <th>Target (₱)</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </table>
                            <tbody id="admin-queue-tbody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Loading operational queues...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin.js"></script>
</body>
</html>