<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Analytics & Operational Reports</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Comprehensive compliance ratios, consent distribution, and user-confirmed dispatch metrics.</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid-cols-4">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Total Contacts</div>
            <div class="stat-value" style="color: var(--accent-primary);"><?= e($metrics['contacts']['total']) ?></div>
            <div class="stat-label"><?= e($metrics['contacts']['valid']) ?> Active in Directory</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Granted Consent</div>
            <div class="stat-value" style="color: var(--accent-wa);"><?= e($metrics['contacts']['granted_consent']) ?></div>
            <div class="stat-label"><?= $metrics['contacts']['total'] > 0 ? round(($metrics['contacts']['granted_consent'] / $metrics['contacts']['total']) * 100, 1) : 0 ?>% Consent Ratio</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-green">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">User Confirmed Actions</div>
            <div class="stat-value" style="color: var(--accent-emerald);"><?= e($metrics['recipients']['user_marked_completed'] ?? 0) ?></div>
            <div class="stat-label">Verified Manual Confirmations</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-green">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Opt-Out Suppressed</div>
            <div class="stat-value" style="color: var(--accent-rose);"><?= e($metrics['contacts']['opted_out']) ?></div>
            <div class="stat-label">Excluded from Campaigns</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-rose">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
        </div>
    </div>
</div>

<!-- Detailed Compliance Chart Grid -->
<div class="grid-cols-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Consent Distribution</h3>
        </div>
        <div style="height: 260px; position: relative;">
            <canvas id="analyticsConsentChart"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Campaign Status Distribution</h3>
        </div>
        <div style="height: 260px; position: relative;">
            <canvas id="campaignStatusChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Consent Analytics Chart
    const ctx1 = document.getElementById('analyticsConsentChart').getContext('2d');
    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Granted', 'Pending / Unspecified', 'Opted-Out'],
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
                legend: { position: 'bottom', labels: { color: '#94a3b8' } }
            }
        }
    });

    // 2. Campaign Status Chart
    const ctx2 = document.getElementById('campaignStatusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Completed / Cleaned', 'In Progress', 'Draft / Review'],
            datasets: [{
                data: [
                    <?= (int)$metrics['campaigns']['completed'] ?>,
                    <?= (int)$metrics['campaigns']['in_progress'] ?>,
                    <?= (int)$metrics['campaigns']['draft'] ?>
                ],
                backgroundColor: ['#10b981', '#6366f1', '#fbbf24'],
                borderColor: '#111827',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#94a3b8' } }
            }
        }
    });
});
</script>
