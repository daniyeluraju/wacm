<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Storage & Automatic Cleanup</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Monitor storage footprints, enforce data retention policies, and execute safe temporary file purges.</p>
    </div>
    <div>
        <form method="POST" action="/storage/cleanup" onsubmit="return confirm('Run manual cleanup on eligible temporary files?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">
                <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Run Cleanup Now
            </button>
        </form>
    </div>
</div>

<!-- Storage Warning if threshold reached -->
<?php if ($storage['is_warning']): ?>
    <div class="card" style="border-left: 4px solid var(--accent-amber); margin-bottom: 1.5rem; background-color: rgba(245, 158, 11, 0.05);">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span class="badge badge-warning">Storage Warning</span>
            <span style="font-size: 0.9rem; color: var(--text-primary);">
                Current usage (<?= e($storage['total_mb']) ?> MB) has exceeded your warning threshold (<?= e($storage['warning_threshold_mb']) ?> MB). Consider running a cleanup.
            </span>
        </div>
    </div>
<?php endif; ?>

<!-- Storage Summary Grid -->
<div class="grid-cols-4">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Total Storage Used</div>
            <div class="stat-value" style="color: #60a5fa;"><?= e($storage['total_formatted']) ?></div>
            <div class="stat-label"><?= e($storage['total_mb']) ?> MB / <?= e($storage['warning_threshold_mb']) ?> MB Warning</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Upload Storage</div>
            <div class="stat-value" style="color: var(--accent-wa);"><?= e($storage['breakdown']['uploads']['formatted']) ?></div>
            <div class="stat-label"><?= e($storage['breakdown']['uploads']['files_count']) ?> Protected Media Files</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-green">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Temporary Files</div>
            <div class="stat-value" style="color: #fbbf24;"><?= e($storage['breakdown']['temporary']['formatted']) ?></div>
            <div class="stat-label"><?= e($storage['breakdown']['temporary']['files_count']) ?> Files Eligible for Purge</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-amber">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">System Logs</div>
            <div class="stat-value" style="color: #c084fc;"><?= e($storage['breakdown']['logs']['formatted']) ?></div>
            <div class="stat-label"><?= e($storage['breakdown']['logs']['files_count']) ?> Audit Files</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-purple">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        </div>
    </div>
</div>

<!-- Storage Directory List -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Storage Directories & Protection Rules</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Directory</th>
                    <th>Path</th>
                    <th>Files</th>
                    <th>Size</th>
                    <th>Policy</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($storage['breakdown'] as $type => $info): ?>
                    <tr>
                        <td><strong>storage/<?= e($type) ?></strong></td>
                        <td><code style="font-size: 0.75rem;"><?= e($info['path']) ?></code></td>
                        <td><?= e($info['files_count']) ?></td>
                        <td><strong><?= e($info['formatted']) ?></strong></td>
                        <td>
                            <?php if ($type === 'temporary' || $type === 'exports'): ?>
                                <span class="badge badge-warning">Auto-Purged (24h)</span>
                            <?php elseif ($type === 'uploads'): ?>
                                <span class="badge badge-success">Protected Media</span>
                            <?php else: ?>
                                <span class="badge badge-info">Retained Logs</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cleanup History Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cleanup Execution History</h3>
        <span class="badge badge-neutral">Audit Logged</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Files Deleted</th>
                    <th>Storage Released</th>
                    <th>Status</th>
                    <th>Completed At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No cleanup operations recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><strong><?= ucfirst(e($h->cleanup_type)) ?></strong></td>
                            <td><?= e($h->files_deleted) ?> files</td>
                            <td><span style="color: var(--accent-wa); font-weight: 600;"><?= format_bytes((int)$h->storage_released_bytes) ?></span></td>
                            <td><span class="badge badge-success"><?= ucfirst(e($h->status)) ?></span></td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y H:i', strtotime($h->completed_at ?? $h->created_at)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
