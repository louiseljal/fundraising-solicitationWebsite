// ==========================================================================
// 1. Core Theme Logic Persistence Matrix
// ==========================================================================

const darkModeToggle = document.getElementById("darkModeToggle");
const darkModeSwitch = document.getElementById("darkModeSwitch");

function setTheme(theme) {
  const isDark = theme === "dark";
  document.documentElement.setAttribute("data-bs-theme", isDark ? "dark" : "light");
  document.body.classList.toggle("dark-mode", isDark);
  if (darkModeToggle) darkModeToggle.checked = isDark;
  if (darkModeSwitch) darkModeSwitch.checked = isDark;
  localStorage.setItem("theme", isDark ? "dark" : "light");
}

// Initial structural theme scan
if (localStorage.getItem("theme") === "dark") {
  setTheme("dark");
} else {
  setTheme("light");
}

// Theme switch listener wrapper
function bindThemeToggle() {
  if (darkModeToggle) {
    darkModeToggle.addEventListener("change", () => setTheme(darkModeToggle.checked ? "dark" : "light"));
  }
  if (darkModeSwitch) {
    darkModeSwitch.addEventListener("change", () => setTheme(darkModeSwitch.checked ? "dark" : "light"));
  }
}

bindThemeToggle();

const savedTheme = localStorage.getItem("theme");
setTheme(savedTheme === "dark" ? "dark" : "light");

// ==========================================================================
// 2. Interactive Visibility Password Toggle Logic
// ==========================================================================
document.querySelectorAll('.toggle-password').forEach(toggleButton => {
  toggleButton.addEventListener('click', function() {
    // Locate the matching input element using the data-target attribute string
    const targetId = this.getAttribute('data-target');
    const inputElement = document.getElementById(targetId);
    const iconElement = this.querySelector('i');

    if (inputElement && iconElement) {
      if (inputElement.type === 'password') {
        inputElement.type = 'text';
        iconElement.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        inputElement.type = 'password';
        iconElement.classList.replace('fa-eye', 'fa-eye-slash');
      }
    }
  });
});


// ==========================================================================
// 3. Analytics Engine & Data Dashboard Loader
// ==========================================================================
async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const result = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(result.message || `Request failed with status ${response.status}`);
    }

    return result;
}

const CAMPAIGN_IMAGES = [
    'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1509099836639-18ba1795216d?auto=format&fit=crop&w=900&q=80',
    'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=900&q=80'
];

function peso(amount, options = {}) {
    return '₱' + Number(amount || 0).toLocaleString(undefined, options);
}

function getCampaignImage(item = {}, index = 0) {
    if (item.image_url) return item.image_url;
    return CAMPAIGN_IMAGES[index % CAMPAIGN_IMAGES.length];
}

function buildPublicTrendFromCampaigns(campaigns) {
    const buckets = new Map();
    campaigns.forEach((campaign) => {
        const rawDate = campaign.created_at || campaign.start_date || new Date().toISOString();
        const date = new Date(rawDate);
        const key = `${date.getFullYear()}-${date.getMonth() + 1}`;
        const current = buckets.get(key) || {
            year: date.getFullYear(),
            month_num: date.getMonth() + 1,
            month_name: date.toLocaleString(undefined, { month: 'short' }),
            monthly_total: 0
        };
        current.monthly_total += Number(campaign.total_raised || campaign.current_raised_cache || 0);
        buckets.set(key, current);
    });
    return Array.from(buckets.values()).sort((a, b) => a.year - b.year || a.month_num - b.month_num);
}

async function loadCampaignsPublic() {
    const result = await fetchJson('api/campaigns.php');
    return Array.isArray(result.campaigns) ? result.campaigns : [];
}

async function loadDashboardData() {
    const hasReportsPage = !!document.getElementById('top_solicitors_body');
    if (!hasReportsPage) return;

    try {
        const result = await fetchJson('api/analytics.php?action=dashboard');

        if (!result.success) {
            console.error('Analytics API returned failure:', result.message || result.error);
            return;
        }

        document.getElementById('metric-total-funds-raised').innerText =
            '₱' + parseFloat(result.kpis?.total_raised || 0).toLocaleString();
        document.getElementById('metric-active-members-count').innerText =
            result.activeMembers || 0;
        document.getElementById('metric-monthly-members-count').innerText =
            result.monthlyMembers || 0;

        renderMonthlyTrendChart(result.trend || []);
        renderCampaignPerformanceChart(result.campaignPerformance || []);
        renderMemberGrowthChart(result.trend || []);
        renderTopSolicitors(result.topSolicitors || []);
    } catch (err) {
        console.error('Error fetching analytics:', err);
        try {
            const campaigns = await loadCampaignsPublic();
            const totalRaised = campaigns.reduce((sum, item) => sum + Number(item.total_raised || item.current_raised_cache || 0), 0);
            const activeCount = campaigns.filter(item => String(item.campaign_status || '').toLowerCase() === 'active').length;
            const trend = buildPublicTrendFromCampaigns(campaigns);
            const performance = campaigns
                .filter(item => String(item.campaign_status || '').toLowerCase() !== 'cancelled')
                .map(item => ({
                    title: item.title,
                    progress_pct: item.progress_pct || (Number(item.goal_amount) ? (Number(item.total_raised || item.current_raised_cache || 0) / Number(item.goal_amount)) * 100 : 0)
                }));

            document.getElementById('metric-total-funds-raised').innerText = peso(totalRaised);
            document.getElementById('metric-active-members-count').innerText = String(activeCount);
            document.getElementById('metric-monthly-members-count').innerText = String(campaigns.length);
            renderMonthlyTrendChart(trend);
            renderCampaignPerformanceChart(performance);
            renderMemberGrowthChart(trend);
            renderTopSolicitors(campaigns.slice(0, 5).map(item => ({
                full_name: item.title,
                total_funds: item.total_raised || item.current_raised_cache || 0
            })));
        } catch (fallbackError) {
            console.error('Reports fallback load failed:', fallbackError);
        }
    }
}

function renderMonthlyTrendChart(trendData) {
    const canvas = document.getElementById('donation_trends');
    if (!canvas) return;

    if (typeof window.Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    if (window.__donationTrendChart) {
        window.__donationTrendChart.destroy();
    }

    window.__donationTrendChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: trendData.map(item => item.month_name || `${item.year}-${item.month_num}`),
            datasets: [{
                label: 'Donations (₱)',
                data: trendData.map(item => Number(item.monthly_total || 0)),
                backgroundColor: 'rgba(40, 167, 69, 0.65)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => '₱' + Number(value).toLocaleString() }
                }
            }
        }
    });
}

function renderCampaignPerformanceChart(campaignData) {
    const canvas = document.getElementById('campaign_performance');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    if (window.__campaignPerformanceChart) {
        window.__campaignPerformanceChart.destroy();
    }

    window.__campaignPerformanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: campaignData.map(item => item.title || 'Campaign'),
            datasets: [{
                data: campaignData.map(item => Number(item.progress_pct || 0)),
                backgroundColor: ['#198754', '#0d6efd', '#6f42c1', '#fd7e14', '#20c997']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

function renderMemberGrowthChart(trendData) {
    const canvas = document.getElementById('member_growth_rate');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    if (window.__memberGrowthChart) {
        window.__memberGrowthChart.destroy();
    }

    window.__memberGrowthChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.map(item => item.month_name || `${item.year}-${item.month_num}`),
            datasets: [{
                label: 'Monthly Raised (₱)',
                data: trendData.map(item => Number(item.monthly_total || 0)),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => '₱' + Number(value).toLocaleString() }
                }
            }
        }
    });
}

function renderTopSolicitors(topSolicitors) {
    const tbody = document.getElementById('top_solicitors_body');
    if (!tbody) return;

    if (!topSolicitors.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted small">No solicitor data available yet.</td></tr>';
        return;
    }

    tbody.innerHTML = topSolicitors.map((item, index) => {
        const totalFunds = Number(item.total_funds || 0);
        return `
            <tr>
                <td class="fw-semibold">${index + 1}. ${item.full_name || 'Unknown Donor'}</td>
                <td class="text-center text-muted">—</td>
                <td class="text-center text-muted">—</td>
                <td class="text-end fw-semibold">₱${totalFunds.toLocaleString()}</td>
            </tr>`;
    }).join('');
}

async function loadHomeDashboard() {
    const totalDonations = document.getElementById('total-donations');
    const activeMembers = document.getElementById('active-members');
    const ongoingCampaigns = document.getElementById('ongoing-campaign');
    const collectionsThisMonth = document.getElementById('collections');
    const pendingPayments = document.getElementById('pending');
    const campaignSummary = document.querySelector('.card.data-card .progress-bar');
    const campaignTitle = document.querySelector('.card.data-card .d-flex.justify-content-between .text-secondary');
    const campaignAmount = document.querySelector('.card.data-card .d-flex.justify-content-between .brand-text-color');
    const scheduleTitle = document.querySelectorAll('.card.data-card .fw-bold.small.text-dark')[1];
    const scheduleMeta = document.querySelectorAll('.card.data-card .text-muted.d-block')[1];
    const announcementTitle = document.querySelectorAll('.card.data-card .fw-bold.small.text-dark')[2];
    const announcementMeta = document.querySelectorAll('.card.data-card .text-muted.d-block')[2];
    const scheduleModal = document.getElementById('scheduleItemsContainer');

    if (!totalDonations && !activeMembers && !ongoingCampaigns && !collectionsThisMonth && !pendingPayments) return;

    try {
        const [campaignResult, announcementResult, membersResult, donationsResult] = await Promise.all([
            fetchJson('api/campaigns.php'),
            fetchJson('api/announcements.php'),
            fetchJson('api/members.php'),
            fetchJson('api/donations.php')
        ]);

        const campaigns = Array.isArray(campaignResult.campaigns) ? campaignResult.campaigns : [];
        const announcements = Array.isArray(announcementResult.announcements) ? announcementResult.announcements : [];
        const memberCount = membersResult.metrics?.totalMembers || 0;
        const donations = Array.isArray(donationsResult.donations) ? donationsResult.donations : [];

        const totalRaised = campaigns.reduce((sum, item) => sum + Number(item.total_raised || 0), 0);
        const activeCount = campaigns.filter(item => String(item.campaign_status || '').toLowerCase() === 'active').length;
        const pendingPaymentsCount = donations.filter(item => item.payment_status === 'Pending').length;
        const topCampaign = campaigns[0] || null;
        const latestAnnouncement = announcements[0] || null;

        if (totalDonations) totalDonations.textContent = '₱' + totalRaised.toLocaleString();
        if (activeMembers) activeMembers.textContent = String(memberCount);
        if (ongoingCampaigns) ongoingCampaigns.textContent = String(activeCount);
        if (collectionsThisMonth) collectionsThisMonth.textContent = '₱' + totalRaised.toLocaleString();
        if (pendingPayments) pendingPayments.textContent = String(pendingPaymentsCount);

        if (campaignSummary && topCampaign) {
            const progress = Number(topCampaign.progress_pct || 0);
            campaignSummary.style.width = `${Math.min(100, progress)}%`;
            campaignSummary.setAttribute('aria-valuenow', String(progress));
        }
        if (campaignTitle && topCampaign) campaignTitle.textContent = topCampaign.title || 'Campaign';
        if (campaignAmount && topCampaign) campaignAmount.textContent = '₱' + Number(topCampaign.goal_amount || 0).toLocaleString();

        if (scheduleTitle) scheduleTitle.textContent = topCampaign ? topCampaign.title : 'No upcoming schedule';
        if (scheduleMeta) scheduleMeta.textContent = topCampaign ? `Ends ${topCampaign.end_date || 'soon'}` : 'No campaign events are available yet.';

        if (announcementTitle) announcementTitle.textContent = latestAnnouncement ? (latestAnnouncement.title || 'Announcement') : 'No announcements yet';
        if (announcementMeta) announcementMeta.textContent = latestAnnouncement ? (latestAnnouncement.content || 'Latest system update') : 'The announcement feed is empty.';

        if (scheduleModal) {
            const items = campaigns.slice(0, 3).map(item => `
                <div class="border rounded-3 p-3 mb-2">
                    <div class="fw-semibold small text-dark">${item.title || 'Campaign'}</div>
                    <small class="text-muted">${item.category || 'General'} • Goal ₱${Number(item.goal_amount || 0).toLocaleString()} • ${item.end_date || 'TBD'}</small>
                </div>`).join('');
            scheduleModal.innerHTML = items || '<p class="text-muted mb-0 small">No campaign schedule items are available yet.</p>';
        }
    } catch (error) {
        console.error('Home dashboard load failed:', error);
        if (totalDonations) totalDonations.textContent = '—';
        if (activeMembers) activeMembers.textContent = '0';
        if (ongoingCampaigns) ongoingCampaigns.textContent = '0';
        if (collectionsThisMonth) collectionsThisMonth.textContent = '—';
        if (pendingPayments) pendingPayments.textContent = '0';
    }
}

async function loadFundraisingCards() {
    const container = document.getElementById('fundraising-cards-container');
    if (!container) return;

    try {
        const result = await fetchJson('api/campaigns.php');
        if (!result.success || !Array.isArray(result.campaigns)) {
            throw new Error('Unable to load campaigns');
        }

        const campaigns = result.campaigns;
        const totalRaised = campaigns.reduce((sum, item) => sum + Number(item.total_raised || 0), 0);
        const activeCampaigns = campaigns.filter(item => String(item.campaign_status).toLowerCase() === 'active').length;
        const completedCampaigns = campaigns.filter(item => String(item.campaign_status).toLowerCase() === 'completed').length;
        const donorCount = campaigns.reduce((sum, item) => sum + Number(item.donor_count || 0), 0);

        document.getElementById('display-total-raised-kpi').textContent = peso(totalRaised, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('display-active-campaigns-count').textContent = String(activeCampaigns);
        document.getElementById('display-total-donors-count').textContent = String(donorCount);
        document.getElementById('display-completed-campaigns-count').textContent = String(completedCampaigns);

        const overallProgress = totalRaised && campaigns.reduce((sum, item) => sum + Number(item.goal_amount || 0), 0)
            ? (totalRaised / campaigns.reduce((sum, item) => sum + Number(item.goal_amount || 0), 0)) * 100
            : 0;
        document.getElementById('display-overall-progress-percentage').textContent = overallProgress.toFixed(1) + '%';
        document.getElementById('display-overall-progress-bar').style.width = overallProgress.toFixed(1) + '%';
        document.getElementById('display-overall-raised-cash').textContent = peso(totalRaised, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' raised';
        document.getElementById('display-overall-target-cash').textContent = 'Goal: ' + peso(campaigns.reduce((sum, item) => sum + Number(item.goal_amount || 0), 0), {minimumFractionDigits: 2, maximumFractionDigits: 2});

        container.innerHTML = campaigns.map((campaign, index) => {
            const raised = Number(campaign.total_raised || 0);
            const goal = Number(campaign.goal_amount || 0);
            const progress = goal > 0 ? Math.min((raised / goal) * 100, 100) : 0;
            const daysLeft = Math.max(0, Math.ceil((new Date(campaign.end_date) - new Date()) / (1000 * 60 * 60 * 24)));
            const imageUrl = getCampaignImage(campaign, index);
            return `
                <div class="col-xl-4 col-md-6 col-12 dynamic-campaign-card-wrapper">
                  <div class="card border-0 h-100 rounded-4 shadow-sm dynamic-card-bg overflow-hidden position-relative">
                    <div class="position-relative ratio ratio-16x9 bg-secondary-subtle card-image-placeholder-zone">
                      <img src="${imageUrl}" alt="${campaign.title || 'Campaign'}" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
                      <div class="position-absolute top-0 start-0 w-100 p-3 d-flex justify-content-between align-items-start z-1">
                        <span class="badge bg-dark bg-opacity-75 rounded-pill px-2 py-1 fs-xs"><i class="bi bi-tag-fill me-1"></i>${campaign.category || 'General'}</span>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fs-xs">${campaign.campaign_status || 'Active'}</span>
                      </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                      <h5 class="fw-bold dynamic-text-main mb-2">${campaign.title || 'Campaign'}</h5>
                      <p class="dynamic-text-muted small text-line-clamp-3 mb-3">${campaign.description || 'No description provided.'}</p>
                      <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-1 font-monospace small">
                          <span class="fw-bold text-success">${peso(raised, {minimumFractionDigits: 2, maximumFractionDigits: 2})} raised</span>
                          <span class="dynamic-text-muted">${progress.toFixed(0)}%</span>
                        </div>
                        <div class="progress rounded-pill mb-2" style="height: 6px;"><div class="progress-bar bg-success" role="progressbar" style="width: ${progress.toFixed(1)}%"></div></div>
                        <div class="dynamic-text-muted font-monospace small mb-3">of ${peso(goal, {minimumFractionDigits: 2, maximumFractionDigits: 2})} goal</div>
                        <div class="d-flex justify-content-between text-muted font-monospace small border-top pt-3 mb-3">
                          <span><i class="bi bi-people me-1"></i> ${campaign.donor_count || 0} donors</span>
                          <span><i class="bi bi-clock me-1"></i> ${daysLeft} days left</span>
                        </div>
                        <div class="row g-2">
                          <div class="col-6"><button type="button" class="btn btn-success btn-sm w-100 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modal-quick-donate-wizard" data-campaign-id="${campaign.campaign_id}"><i class="bi bi-heart-fill small"></i> Donate</button></div>
                          <div class="col-6"><button type="button" class="btn btn-outline-secondary btn-sm w-100 rounded-pill fw-bold text-muted dynamic-outline-btn d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modal-campaign-detail-viewer" data-campaign-id="${campaign.campaign_id}"><i class="bi bi-eye"></i> View</button></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>`;
        }).join('');

        const donateButtons = container.querySelectorAll('[data-campaign-id]');
        donateButtons.forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('input-donate-campaign-id').value = button.dataset.campaignId;
            });
        });

    } catch (error) {
        console.error('Failed to load campaigns:', error);
        container.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">Campaign data could not be loaded right now.</div></div>';
    }
}

async function loadSolicitationsPage() {
    const container = document.getElementById('solicitations-row-container');
    if (!container) return;

    try {
        const result = await fetchJson('api/solicitations.php');
        const solicitations = Array.isArray(result.solicitations) ? result.solicitations : [];

        if (!solicitations.length) {
            container.innerHTML = '<div class="col-12"><div class="alert alert-light border mb-0">No approved solicitations are available yet.</div></div>';
            document.getElementById('metric-total-posts').textContent = '0';
            document.getElementById('metric-critical-posts').textContent = '0';
            document.getElementById('metric-fulfilled-posts').textContent = '0';
            return;
        }

        container.innerHTML = solicitations.map((item, index) => {
            const status = String(item.status || 'Pending');
            const urgency = String(item.urgency_level || 'Medium');
            const statusSlug = status.toLowerCase() === 'completed' ? 'fulfilled' : 'open';
            const urgencySlug = urgency.toLowerCase();
            const categorySlug = String(item.solicitation_category || '').toLowerCase();
            const target = Number(item.target_amount || 0);
            const imageUrl = getCampaignImage({ category: item.solicitation_category, title: item.post_title }, index);
            const statusClass = statusSlug === 'fulfilled' ? 'status-pill-completed' : 'status-pill-active';
            const urgencyClass = urgencySlug === 'high' ? 'status-pill-ending' : 'status-pill-active';

            return `
                <div class="col solicitation-post-card" data-card-status="${statusSlug}" data-card-urgency="${urgencySlug}" data-card-category="${categorySlug}">
                    <div class="card solicitation-card dynamic-card-bg h-100 cursor-pointer logic-card-trigger"
                         data-title="${item.post_title || ''}"
                         data-category="${item.solicitation_category || ''}"
                         data-urgency="${urgency}"
                         data-urgency-badge-class="${urgencyClass}"
                         data-status="${status}"
                         data-status-badge-class="${statusClass}"
                         data-target="${target.toLocaleString()}"
                         data-deadline="${item.campaign_deadline || ''}"
                         data-beneficiaries="${item.beneficiary_count || 'General community'}"
                         data-urgency-level="${urgency}"
                         data-contact-name="${item.poc_name || `${item.first_name || ''} ${item.last_name || ''}`.trim()}"
                         data-contact-phone="${item.poc_phone || ''}"
                         data-contact-email="${item.username || ''}"
data-description="${item.post_description || ''}" data-allocation-items='${(item.allocation_items_json ? String(item.allocation_items_json) : []).toString().replace(/'/g, '&#39;')}' data-attachments='${(item.attachments_json ? String(item.attachments_json) : []).toString().replace(/'/g, '&#39;')}'>
                        <div class="card-img-container overflow-hidden">
                            <img src="${imageUrl}" alt="${item.post_title || 'Solicitation'}" class="w-100 h-100 object-fit-cover">
                            <span class="badge-urgency bg-dark text-white">${urgency}</span>
                            <span class="badge-status ${statusClass} shadow-sm position-absolute top-0 end-0 m-2">${status}</span>
                            <span class="card-category-tag text-white">${item.solicitation_category || 'General'}</span>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <h6 class="fw-bold mb-2 dynamic-text-main text-truncate">${item.post_title || 'Solicitation'}</h6>
                            <p class="text-muted small mb-3 text-line-clamp-2">${item.post_description || 'No description provided.'}</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between text-muted small mb-1 font-monospace x-small">
                                    <span class="fw-bold text-success">${peso(target)}</span>
                                    <span>Target</span>
                                </div>
                                <div class="progress rounded-pill mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 15%"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted x-small border-top pt-2 font-monospace">
                                    <span><i class="bi bi-person me-1"></i>${item.first_name || 'User'}</span>
                                    <span><i class="bi bi-clock me-1"></i>${item.campaign_deadline || '--'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
        }).join('');

        document.getElementById('metric-total-posts').textContent = String(solicitations.length);
        document.getElementById('metric-critical-posts').textContent = String(solicitations.filter(item => ['High', 'Critical'].includes(item.urgency_level)).length);
        document.getElementById('metric-fulfilled-posts').textContent = String(solicitations.filter(item => item.status === 'Completed').length);
        
        // Initialize filter functionality
        executeDashboardFilters();
    } catch (error) {
        console.error('Solicitations load failed:', error);
        container.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">Log in to view solicitation records, or create a new solicitation from the Campaigns menu.</div></div>';
    }
}

function executeDashboardFilters() {
    const statusFilter = document.getElementById('filter-status')?.value || 'all';
    const urgencyFilter = document.getElementById('filter-urgency')?.value || 'all';
    const categoryFilter = document.getElementById('filter-category')?.value || 'all';
    const searchFilter = document.getElementById('filter-search')?.value?.toLowerCase() || '';

    const cards = document.querySelectorAll('.solicitation-post-card');
    
    cards.forEach(card => {
        const cardStatus = card.dataset.cardStatus || '';
        const cardUrgency = card.dataset.cardUrgency || '';
        const cardCategory = card.dataset.cardCategory || '';
        const cardTitle = card.querySelector('h6')?.textContent?.toLowerCase() || '';

        const matchesStatus = statusFilter === 'all' || cardStatus === statusFilter;
        const matchesUrgency = urgencyFilter === 'all' || cardUrgency === urgencyFilter;
        const matchesCategory = categoryFilter === 'all' || cardCategory === categoryFilter;
        const matchesSearch = searchFilter === '' || cardTitle.includes(searchFilter);

        if (matchesStatus && matchesUrgency && matchesCategory && matchesSearch) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

async function loadMembershipPage() {
    if (!document.getElementById('total-members-count')) return;

    try {
        const result = await fetchJson('api/members.php');
        const metrics = result.metrics || {};
        const recentMembers = Array.isArray(result.recentMembers) ? result.recentMembers : [];
        const admins = Array.isArray(result.admins) ? result.admins : [];

        const totalMembers = metrics.totalMembers || 0;
        const activeMembers = metrics.activeMembers || 0;
        const activeCollections = metrics.activeCollections || 0;
        const totalFundsRaised = metrics.totalFundsRaised || 0;

        document.getElementById('total-members-count').textContent = String(totalMembers);
        document.getElementById('active-members-count').textContent = String(activeMembers);
        document.getElementById('active-collections-count').textContent = String(activeCollections);
        document.getElementById('total-funds-raised').textContent = peso(totalFundsRaised);

        const renderMember = item => `
            <div class="d-flex align-items-center gap-2">
                <div class="member-avatar-badge rounded-circle d-flex align-items-center justify-content-center">${(item.username || 'U').charAt(0).toUpperCase()}</div>
                <div class="min-w-0">
                    <div class="fw-semibold small dynamic-text-main text-truncate">${item.username || 'User'}</div>
                    <small class="text-muted">${item.role || 'Member'} • ${item.joined_date || 'Recently'}</small>
                </div>
            </div>`;

        document.getElementById('recent-members-list-container').innerHTML = recentMembers.slice(0, 5).map(renderMember).join('') || '<p class="text-muted small mb-0">No members yet.</p>';
        document.getElementById('administrators-list-container').innerHTML = admins.slice(0, 5).map(renderMember).join('') || '<p class="text-muted small mb-0">No administrators found.</p>';
    } catch (error) {
        console.error('Membership load failed:', error);
        try {
            const campaigns = await loadCampaignsPublic();
            const totalRaised = campaigns.reduce((sum, item) => sum + Number(item.total_raised || 0), 0);
            document.getElementById('total-members-count').textContent = '--';
            document.getElementById('active-members-count').textContent = '--';
            document.getElementById('active-collections-count').textContent = String(campaigns.filter(item => item.campaign_status === 'Active').length);
            document.getElementById('total-funds-raised').textContent = peso(totalRaised);
            document.getElementById('recent-members-list-container').innerHTML = '<p class="text-muted small mb-0">Member details require login.</p>';
            document.getElementById('administrators-list-container').innerHTML = '<p class="text-muted small mb-0">Administrator details require login.</p>';
        } catch {}
    }
}

async function loadDonationsPage() {
    const tbody = document.getElementById('donations_body');
    if (!tbody) return;

    try {
        const result = await fetchJson('api/donations.php');
        const donations = Array.isArray(result.donations) ? result.donations : [];
        tbody.innerHTML = donations.map(item => `
            <tr>
                <td>${item.created_at ? String(item.created_at).slice(0, 10) : ''}</td>
                <td>${[item.first_name, item.last_name].filter(Boolean).join(' ') || item.username || 'Donor'}</td>
                <td>${item.campaign_title || 'Campaign'}</td>
                <td class="fw-semibold text-success">${peso(item.amount)}</td>
                <td><span class="badge-status ${item.payment_status === 'Completed' ? 'badge-status-confirmed' : 'badge-status-pending'}">${item.payment_status || 'Pending'}</span></td>
            </tr>`).join('') || '<tr><td colspan="5" class="text-center py-5 text-muted small">No donation records yet.</td></tr>';

        const completed = donations.filter(item => item.payment_status === 'Completed');
        const avg = completed.length ? completed.reduce((sum, item) => sum + Number(item.amount || 0), 0) / completed.length : 0;
        const topDonors = [...completed]
            .sort((a, b) => Number(b.amount || 0) - Number(a.amount || 0))
            .slice(0, 5);
        document.getElementById('insight-average-donation').textContent = peso(avg);
        document.getElementById('insight-solicitors-count').textContent = String(new Set(completed.map(item => item.user_id)).size);
        document.getElementById('top_donors').innerHTML = topDonors.map((item, index) => `
            <div class="top-donor-item d-flex justify-content-between align-items-center">
                <span><span class="donor-rank-number">${index + 1}</span> ${[item.first_name, item.last_name].filter(Boolean).join(' ') || item.username || 'Donor'}</span>
                <strong>${peso(item.amount)}</strong>
            </div>`).join('') || '<div class="p-3 text-center text-muted small bg-light rounded-3">No completed donations yet.</div>';
    } catch (error) {
        console.error('Donations load failed:', error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted small">Donation records require login.</td></tr>';
    }
}

async function loadUserDashboard() {
    const activityContainer = document.getElementById('recent-activity-container');
    const profileContainer = document.getElementById('profile-overview-container');
    
    if (!activityContainer && !profileContainer) return;

    try {
        const sessionResult = await fetchJson('api/session_check.php');
        
        if (!sessionResult.logged_in) {
            if (activityContainer) activityContainer.innerHTML = '<p class="text-muted small mb-0">Please log in to view your activity.</p>';
            if (profileContainer) profileContainer.innerHTML = '<p class="text-muted small mb-0">Please log in to view your profile.</p>';
            return;
        }

        // Load user's donations
        const donationsResult = await fetchJson('api/donations.php');
        const donations = Array.isArray(donationsResult.donations) ? donationsResult.donations : [];
        const userDonations = donations.filter(d => d.username === sessionResult.username).slice(0, 5);

        // Load user's solicitations
        const solicitationsResult = await fetchJson('api/solicitations.php?action=my_submissions');
        const solicitations = Array.isArray(solicitationsResult.solicitations) ? solicitationsResult.solicitations : [];

        // Display recent activity
        if (activityContainer) {
            if (userDonations.length === 0 && solicitations.length === 0) {
                activityContainer.innerHTML = '<p class="text-muted small mb-0">No recent activity yet.</p>';
            } else {
                let activityHtml = '<div class="list-group list-group-flush">';
                
                userDonations.forEach(d => {
                    activityHtml += `
                        <div class="list-group-item px-0 py-2 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="fw-semibold">Donated ${peso(d.amount)} to ${d.campaign_title || 'Campaign'}</small>
                                <small class="text-muted">${d.created_at ? String(d.created_at).slice(0, 10) : ''}</small>
                            </div>
                        </div>`;
                });

                solicitations.slice(0, 3).forEach(s => {
                    activityHtml += `
                        <div class="list-group-item px-0 py-2 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="fw-semibold">Created solicitation: ${s.post_title || 'Untitled'}</small>
                                <small class="badge bg-${s.status === 'Approved' ? 'success' : 'warning'}">${s.status || 'Pending'}</small>
                            </div>
                        </div>`;
                });

                activityHtml += '</div>';
                activityContainer.innerHTML = activityHtml;
            }
        }

        // Display profile overview
        if (profileContainer) {
            const totalDonated = userDonations
                .filter(d => d.payment_status === 'Completed')
                .reduce((sum, d) => sum + Number(d.amount || 0), 0);
            
            const activeSolicitations = solicitations.filter(s => s.status === 'Pending' || s.status === 'Approved').length;

            profileContainer.innerHTML = `
                <div class="mb-2">
                    <small class="text-muted">Total Donated</small>
                    <div class="fw-bold text-success">${peso(totalDonated)}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Active Solicitations</small>
                    <div class="fw-bold">${activeSolicitations}</div>
                </div>
                <div>
                    <small class="text-muted">Account Status</small>
                    <div class="fw-bold text-success">Active</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('User dashboard load failed:', error);
        if (activityContainer) activityContainer.innerHTML = '<p class="text-muted small mb-0">Unable to load activity data.</p>';
        if (profileContainer) profileContainer.innerHTML = '<p class="text-muted small mb-0">Unable to load profile data.</p>';
    }
}

async function loadAnnouncementsPage() {
    const container = document.getElementById('announcement-cards-container');
    if (!container) return;

    try {
        const result = await fetchJson('api/announcements.php');
        const announcements = Array.isArray(result.announcements) ? result.announcements : [];

        if (!announcements.length) {
            container.innerHTML = '<div class="col-12"><div class="alert alert-light border mb-0">No announcements available yet.</div></div>';
            return;
        }

        container.innerHTML = announcements.map(item => {
            const priorityClass = item.priority === 'Urgent' ? 'border-danger' : item.priority === 'Important' ? 'border-warning' : 'border-secondary';
            const badgeClass = item.priority === 'Urgent' ? 'bg-danger' : item.priority === 'Important' ? 'bg-warning text-dark' : 'bg-secondary';
            
            return `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 rounded-4 shadow-sm h-100 ${priorityClass}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge ${badgeClass}">${item.priority || 'Normal'}</span>
                                ${item.is_pinned ? '<i class="bi bi-pin-fill text-primary"></i>' : ''}
                            </div>
                            <h5 class="fw-bold mb-2">${item.title || 'Announcement'}</h5>
                            <p class="text-muted small mb-3">${item.content || 'No content provided.'}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">By ${[item.first_name, item.last_name].filter(Boolean).join(' ') || 'Admin'}</small>
                                <small class="text-muted">${item.created_at ? String(item.created_at).slice(0, 10) : ''}</small>
                            </div>
                        </div>
                    </div>
                </div>`;
        }).join('');
    } catch (error) {
        console.error('Announcements load failed:', error);
        container.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">Unable to load announcements. Please try again later.</div></div>';
    }
}

async function loadCollectionsPage() {
    const container = document.getElementById('collections-cards-container');
    if (!container) return;

    try {
        const result = await fetchJson('api/collections.php');
        const collections = Array.isArray(result.collections) ? result.collections : [];
        const totalCollected = result.total_collected || 0;

        // Update total collected display if exists
        const totalElement = document.getElementById('total-collections-amount');
        if (totalElement) {
            totalElement.textContent = peso(totalCollected);
        }

        if (!collections.length) {
            container.innerHTML = '<div class="col-12"><div class="alert alert-light border mb-0">No collections recorded yet.</div></div>';
            return;
        }

        container.innerHTML = collections.map(item => {
            const progress = item.goal_amount ? Math.min((item.amount / item.goal_amount) * 100, 100) : 0;
            
            return `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 rounded-4 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-2">${item.campaign_title || 'Campaign'}</h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Collected by ${[item.first_name, item.last_name].filter(Boolean).join(' ') || 'Unknown'}</small>
                                <small class="text-muted">${item.collection_date || ''}</small>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold text-success">${peso(item.amount)}</span>
                                    <span class="text-muted">${progress.toFixed(0)}%</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: ${progress}%"></div>
                                </div>
                            </div>
                            <small class="text-muted">Method: ${item.collection_method || 'Cash'}</small>
                            ${item.notes ? `<p class="text-muted small mt-2 mb-0">${item.notes}</p>` : ''}
                        </div>
                    </div>
                </div>`;
        }).join('');
    } catch (error) {
        console.error('Collections load failed:', error);
        container.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">Unable to load collections. Please log in to view collection records.</div></div>';
    }
}

function bindRegistrationForm() {
    const registrationForm = document.getElementById('form-registration');
    if (!registrationForm) return;

    registrationForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            const result = await fetchJson('register_process.php', {
                method: 'POST',
                body: new FormData(registrationForm)
            });

            alert(result.message || 'Registration successful.');
            if (result.success && result.redirect) {
                window.location.href = result.redirect;
            }
        } catch (error) {
            console.error('Registration failed:', error);
            alert(error.message || 'Unable to create your account right now.');
        }
    });
}

function bindLoginForm() {
    const loginForm = document.getElementById('form-login');
    if (!loginForm) return;

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            const result = await fetchJson('login_process.php', {
                method: 'POST',
                body: new FormData(loginForm)
            });

            alert(result.message || 'Login successful.');
            if (result.success && result.redirect) {
                window.location.href = result.redirect;
            }
        } catch (error) {
            console.error('Login failed:', error);
            alert(error.message || 'Unable to sign in right now.');
        }
    });
}

function bindRoleAssignment() {
    const roleForm = document.getElementById('form-role-assignment');
    if (!roleForm) return;

    roleForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(roleForm);
        formData.set('action', 'change_role');

        try {
            const result = await fetchJson('api/members.php', {
                method: 'POST',
                body: formData
            });

            alert(result.message || 'Role updated.');
            roleForm.reset();
        } catch (error) {
            console.error('Role update failed:', error);
            alert(error.message || 'Unable to update user role right now.');
        }
    });
}

function isAdminRoleLabel(role = '') {
    return String(role || '').trim().toLowerCase() === 'admin';
}

function updateAdminOnlyControls(role = '') {
    const rolePanel = document.getElementById('role-assignment-card');
    const warning = document.getElementById('admin-access-warning');
    const canManageRoles = isAdminRoleLabel(role);

    if (rolePanel) rolePanel.classList.toggle('d-none', !canManageRoles);
    if (warning) warning.classList.toggle('d-none', canManageRoles);
}

async function loadSessionUser() {
    const nameEl = document.getElementById('user-display-name');
    const roleEl = document.getElementById('user-display-role');
    const avatarEl = document.getElementById('user-avatar-initial');

    if (!nameEl && !roleEl && !avatarEl) return;

    try {
        const result = await fetchJson('api/session_check.php');

        if (result.logged_in) {
            const fullName = result.full_name || result.username || 'User';
            const role = result.user_role || 'User';

            if (nameEl) nameEl.textContent = fullName;
            updateAdminOnlyControls(role);
            if (roleEl) roleEl.textContent = role;
            if (avatarEl) avatarEl.textContent = fullName.charAt(0).toUpperCase();
        } else {
            if (nameEl) nameEl.textContent = 'Guest';
            if (roleEl) roleEl.textContent = 'User';
            if (avatarEl) avatarEl.textContent = 'G';
            updateAdminOnlyControls('User');
        }
    } catch (error) {
        console.warn('Session check failed:', error);
        if (nameEl) nameEl.textContent = 'Guest';
        if (roleEl) roleEl.textContent = 'User';
        if (avatarEl) avatarEl.textContent = 'G';
        updateAdminOnlyControls('User');
    }
}

function bindInteractiveButtons() {
    const createCampaignButton = document.getElementById('btn-trigger-create-campaign');
    if (createCampaignButton) {
        createCampaignButton.addEventListener('click', () => {
            window.location.href = 'create_solicitation.html';
        });
    }

    const createSolicitationBanner = document.getElementById('btn-banner-create-solicitation');
    if (createSolicitationBanner) {
        createSolicitationBanner.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = 'create_solicitation.html';
        });
    }

    const quickDonationForm = document.getElementById('form-quick-donate-submission');
    if (quickDonationForm) {
        // Set campaign_id when donate button is clicked
        document.querySelectorAll('[data-bs-target="#modal-quick-donate-wizard"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const campaignId = btn.getAttribute('data-campaign-id');
                if (campaignId) {
                    document.getElementById('input-donate-campaign-id').value = campaignId;
                }
            });
        });

        quickDonationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(quickDonationForm);
            formData.set('action', 'donate');

            try {
                const result = await fetchJson('api/donations.php', {
                    method: 'POST',
                    body: formData
                });

                alert(result.message || 'Donation request completed.');
                if (result.success) {
                    quickDonationForm.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-quick-donate-wizard'));
                    if (modal) modal.hide();
                    // Reload the page to show updated donation amounts
                    location.reload();
                }
            } catch (error) {
                console.error('Donation submission failed:', error);
                alert('Unable to submit donation right now.');
            }
        });
    }

    const createSolicitationForm = document.getElementById('form-create-solicitation');
    if (createSolicitationForm) {
        // Add allocation row functionality
        const addAllocationBtn = document.getElementById('btn-add-allocation-row');
        const allocationContainer = document.getElementById('container-dynamic-allocation-rows');
        
        if (addAllocationBtn && allocationContainer) {
            addAllocationBtn.addEventListener('click', () => {
                const newRow = document.createElement('div');
                newRow.className = 'row g-2 align-items-center allocation-item-row mb-2';
                newRow.innerHTML = `
                    <div class="col-10">
                        <input type="text" class="form-control form-control-sm rounded-3 dynamic-input-bg" name="allocation_items[]" placeholder="e.g., Sound System Rental Allocation">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-3 remove-allocation-row"><i class="bi bi-trash-fill"></i></button>
                    </div>
                `;
                allocationContainer.appendChild(newRow);
                
                // Add remove functionality to the new button
                newRow.querySelector('.remove-allocation-row').addEventListener('click', () => {
                    newRow.remove();
                });
            });
            
            // Add remove functionality to existing delete buttons
            allocationContainer.querySelectorAll('.remove-allocation-row').forEach(btn => {
                btn.addEventListener('click', () => {
                    btn.closest('.allocation-item-row').remove();
                });
            });
        }
        
        // Preview and Save Draft button functionality (robust selector)
        try {
            const inlineButtons = Array.from(createSolicitationForm.querySelectorAll('button[type="button"]'));
            const previewBtn = inlineButtons.find(b => b.textContent && b.textContent.trim().toLowerCase().includes('preview'));
            const saveDraftBtn = inlineButtons.find(b => b.textContent && b.textContent.trim().toLowerCase().includes('save draft'));

            if (previewBtn) {
                previewBtn.addEventListener('click', () => {
                    alert('Preview functionality coming soon. This will show how your solicitation will appear to other users.');
                });
            }

            if (saveDraftBtn) {
                saveDraftBtn.addEventListener('click', () => {
                    alert('Save draft functionality coming soon. This will save your solicitation as a draft for later completion.');
                });
            }
        } catch (e) {
            // Fail silently if DOM selection errors occur
            console.warn('Solicitation inline buttons unavailable', e);
        }
        
        // Character counter for description
        const descriptionTextarea = document.getElementById('textarea-description');
        const charCounter = document.getElementById('id-character-counter');
        if (descriptionTextarea && charCounter) {
            descriptionTextarea.addEventListener('input', () => {
                const current = descriptionTextarea.value.length;
                const max = descriptionTextarea.maxLength;
                charCounter.textContent = `${current} / ${max} characters`;
            });
        }
        
        createSolicitationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(createSolicitationForm);
            formData.set('action', 'create');
            if (formData.get('solicitation_category') === 'Other') {
                const customCategory = String(formData.get('custom_category_title') || '').trim();
                if (customCategory) {
                    formData.set('solicitation_category', customCategory);
                }
            }

            try {
                // Use classic form submit to preserve multipart/form-data (file uploads).
                // PHP routing in api/solicitations.php depends on $_POST['action'].
                // Ensure action is included as a real form field.

                let actionInput = createSolicitationForm.querySelector('input[name="action"][value="create"]');
                if (!actionInput) {
                    actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'create';
                    createSolicitationForm.appendChild(actionInput);
                }

                // Temporarily switch to non-AJAX submit
                createSolicitationForm.action = 'api/solicitations.php';
                createSolicitationForm.method = 'POST';
                createSolicitationForm.enctype = 'multipart/form-data';

                // Submit and stop JS handling
                createSolicitationForm.submit();
                return;
            } catch (error) {
                console.error('Solicitation submission failed:', error);
                alert(error.message || 'Unable to create the solicitation right now.');
            }
        });
    }

    const exportPdfButton = document.getElementById('btn-export-pdf');
    if (exportPdfButton) {
        exportPdfButton.addEventListener('click', () => {
            window.location.href = 'api/export.php?type=pdf&report=analytics';
        });
    }

    const exportCsvButton = document.getElementById('btn-export-csv');
    if (exportCsvButton) {
        exportCsvButton.addEventListener('click', () => {
            window.location.href = 'api/export.php?type=csv&report=analytics';
        });
    }

    const shareLinkButton = document.getElementById('btn-share-report-link');
    if (shareLinkButton) {
        shareLinkButton.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                alert('Report link copied to clipboard.');
            } catch (error) {
                console.error('Copy failed:', error);
                alert('Copy failed. Please copy the URL manually.');
            }
        });
    }

    const exportDonationsPdfButton = document.getElementById('btn-export-donations-pdf');
    if (exportDonationsPdfButton) {
        exportDonationsPdfButton.addEventListener('click', () => {
            window.location.href = 'api/export.php?type=pdf&report=donations';
        });
    }

    const notificationBell = document.getElementById('notification-bell');
    if (notificationBell) {
        notificationBell.addEventListener('click', () => {
            alert('You currently have no new notifications.');
        });
    }

    const mailButton = document.getElementById('mail-button');
    if (mailButton) {
        mailButton.addEventListener('click', (event) => {
            event.preventDefault();
            window.location.href = 'mailto:miko.admin@hopefund.org?subject=Support%20Request';
        });
    }
}

// Run on load
document.addEventListener('DOMContentLoaded', () => {
    bindLoginForm();
    bindRegistrationForm();
    loadSessionUser();
    loadDashboardData();
    loadHomeDashboard();
    loadFundraisingCards();
    loadUserDashboard();
    loadAnnouncementsPage();
    loadCollectionsPage();
    loadSolicitationsPage();
    loadMembershipPage();
    loadDonationsPage();
    bindRoleAssignment();
    bindInteractiveButtons();
});
