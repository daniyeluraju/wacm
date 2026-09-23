<!-- Compliance Notice -->
<div class="compliance-banner">
    <div class="compliance-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
    </div>
    <div class="compliance-text">
        <h4>WhatsApp Productivity & Consent Control</h4>
        <p>
            Welcome to WACM. Prepare messages, review recipient eligibility, and manage user-paced manual communication with zero platform violations.
        </p>
    </div>
</div>

<!-- Primary Stats Grid -->
<div class="grid-cols-4">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Total Contacts</div>
            <div class="stat-value" style="color: var(--accent-primary);"><?= e($metrics['contacts']['total']) ?></div>
            <div class="stat-label"><?= e($metrics['contacts']['valid']) ?> Active &bull; <?= e($metrics['contacts']['opted_out']) ?> Opted Out</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Granted Consent</div>
            <div class="stat-value" style="color: var(--accent-wa);"><?= e($metrics['contacts']['granted_consent']) ?></div>
            <div class="stat-label"><?= e($metrics['contacts']['pending_consent']) ?> Unspecified / Pending</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-green">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Active Campaigns</div>
            <div class="stat-value" style="color: #c084fc;"><?= e($metrics['campaigns']['in_progress']) ?></div>
            <div class="stat-label"><?= e($metrics['campaigns']['total']) ?> Total &bull; <?= e($metrics['campaigns']['completed']) ?> Completed</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-purple">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Storage Used</div>
            <div class="stat-value" style="color: #fbbf24;"><?= e($metrics['storage']['total_formatted']) ?></div>
            <div class="stat-label"><?= e($metrics['storage']['total_mb']) ?> MB / <?= e($metrics['storage']['warning_threshold_mb']) ?> MB Limit</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-amber">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid-cols-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                Consent & Compliance Breakdown
            </h3>
        </div>
        <div style="height: 250px; position: relative;">
            <canvas id="consentChart"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                Campaign Dispatch Outcomes
            </h3>
        </div>
        <div style="height: 250px; position: relative;">
            <canvas id="outcomesChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activities and Campaigns -->
<div class="grid-cols-2">
    <!-- Recent Campaigns -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Active & Recent Campaigns</h3>
            <a href="/campaigns/create" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">+ New Campaign</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentCampaigns)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No campaigns created yet. <a href="/campaigns/create">Create one now</a>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentCampaigns as $camp): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?= e($camp->name) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($camp->created_at)) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $camp->status === 'in_progress' ? 'success' : ($camp->status === 'completed' ? 'info' : 'warning') ?>">
                                        <?= strtoupper(str_replace('_', ' ', $camp->status)) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/campaigns/<?= e($camp->id) ?>" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent System Activity</h3>
            <a href="/activity" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentActivities)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No recent logs.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $act): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?= e($act->action_type) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($act->notes ?? 'Action logged') ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $act->status === 'Success' ? 'success' : 'warning' ?>"><?= e($act->status) ?></span>
                                </td>
                                <td style="font-size: 0.75rem; color: var(--text-muted);"><?= e($act->created_at) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Consent Chart
    const ctx1 = document.getElementById('consentChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Granted Consent', 'Pending / Unspecified', 'Opted Out'],
            datasets: [{
                data: [
                    <?= (int)$metrics['contacts']['granted_consent'] ?>,
                    <?= (int)$metrics['contacts']['pending_consent'] ?>,
                    <?= (int)$metrics['contacts']['opted_out'] ?>
                ],
                backgroundColor: ['#25d366', '#3b82f6', '#f43f5e'],
                borderColor: '#111827',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#94a3b8', font: { family: 'Inter' } } }
            }
        }
    });

    // 2. Outcomes Chart
    const ctx2 = document.getElementById('outcomesChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Completed', 'Chat Opened', 'Skipped', 'Eligible'],
            datasets: [{
                label: 'Recipients',
                data: [
                    <?= (int)($metrics['recipients']['user_marked_completed'] ?? 0) ?>,
                    <?= (int)($metrics['recipients']['chat_opened'] ?? 0) ?>,
                    <?= (int)($metrics['recipients']['skipped'] ?? 0) ?>,
                    <?= (int)($metrics['recipients']['eligible'] ?? 0) ?>
                ],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#6366f1'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#1f293d' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            }
        }
    });
});
</script>
