<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">WhatsApp Web Manual Assistant</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Launch safe, user-paced manual communication sessions for active cohorts.</p>
    </div>
    <div>
        <a href="/campaigns/create" class="btn btn-primary">+ New Campaign</a>
    </div>
</div>

<!-- Active Campaigns List -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">
            <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            In-Progress & Ready Campaigns
        </h3>
        <span class="badge badge-success">Manual Workflow</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Status</th>
                    <th>Pacing Interval</th>
                    <th>Created</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $allReady = array_merge($activeCampaigns ?? [], $readyCampaigns ?? []);
                ?>
                <?php if (empty($allReady)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No active campaigns waiting for dispatch. <a href="/campaigns/create">Create a campaign</a> to start the assistant.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($allReady as $camp): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);"><?= e($camp->name) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($camp->description ?? 'No description') ?></div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $camp->status === 'in_progress' ? 'success' : 'info' ?>">
                                    <?= strtoupper(str_replace('_', ' ', $camp->status)) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--text-secondary); font-family: var(--font-mono);"><?= e($camp->pacing_min_seconds) ?> &ndash; <?= e($camp->pacing_max_seconds) ?>s</span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($camp->created_at)) ?></td>
                            <td style="text-align: right;">
                                <a href="/assistant/<?= e($camp->id) ?>" class="btn btn-whatsapp" style="padding: 0.4rem 0.9rem; font-size: 0.8rem;">
                                    Open Assistant Console &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
