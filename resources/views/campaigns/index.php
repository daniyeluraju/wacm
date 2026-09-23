<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Campaign Management</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Organize recipient cohorts, review consent eligibility, and monitor campaign lifecycle.</p>
    </div>
    <div>
        <a href="/campaigns/create" class="btn btn-primary">
            <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create New Campaign
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            All Campaigns (<?= count($campaigns) ?>)
        </h3>
        <span class="badge badge-info">Paced Manual Workflow</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Campaign Name</th>
                    <th>Status</th>
                    <th>Pacing Reminder</th>
                    <th>Created At</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($campaigns)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No campaigns created yet. <a href="/campaigns/create">Create your first campaign</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($campaigns as $camp): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary); font-size: 0.95rem;"><?= e($camp->name) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($camp->description ?? 'No description') ?></div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $camp->status === 'in_progress' ? 'success' : ($camp->status === 'completed' || $camp->status === 'cleaned_up' ? 'info' : ($camp->status === 'paused' ? 'warning' : 'neutral')) ?>">
                                    <?= strtoupper(str_replace('_', ' ', $camp->status)) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--text-secondary); font-family: var(--font-mono);"><?= e($camp->pacing_min_seconds) ?> &ndash; <?= e($camp->pacing_max_seconds) ?>s</span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($camp->created_at)) ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                    <a href="/campaigns/<?= e($camp->id) ?>" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">
                                        Review &amp; Details
                                    </a>
                                    <?php if ($camp->status === 'in_progress' || $camp->status === 'ready_for_review'): ?>
                                        <a href="/assistant/<?= e($camp->id) ?>" class="btn btn-whatsapp" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">
                                            Open Assistant &rarr;
                                        </a>
                                    <?php endif; ?>
                                    <form method="POST" action="/campaigns/<?= e($camp->id) ?>/delete" style="display:inline;" onsubmit="return confirm('Delete campaign \"<?= addslashes(e($camp->name)) ?>\"?\n\nThis will permanently remove the campaign and all its recipients. This action cannot be undone.')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn" style="padding: 0.35rem 0.65rem; font-size: 0.75rem; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.35); border-radius: var(--radius-sm); cursor: pointer;" title="Delete campaign">
                                            🗑 Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
