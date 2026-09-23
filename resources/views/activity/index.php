<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Activity History & Audit Logs</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Inspect immutable security event records, compliance logs, and operator activities.</p>
    </div>
    <div>
        <a href="/activity/export" class="btn btn-secondary">
            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export Activity CSV
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="/activity" style="display: flex; gap: 1rem; align-items: end;">
        <div style="flex: 1;">
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Filter by Action Type</label>
            <select name="action" style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                <option value="">All Action Types</option>
                <option value="Login" <?= ($actionFilter ?? '') === 'Login' ? 'selected' : '' ?>>Login</option>
                <option value="Logout" <?= ($actionFilter ?? '') === 'Logout' ? 'selected' : '' ?>>Logout</option>
                <option value="Contact Created" <?= ($actionFilter ?? '') === 'Contact Created' ? 'selected' : '' ?>>Contact Created</option>
                <option value="Contact Imported" <?= ($actionFilter ?? '') === 'Contact Imported' ? 'selected' : '' ?>>Contact Imported</option>
                <option value="Campaign Created" <?= ($actionFilter ?? '') === 'Campaign Created' ? 'selected' : '' ?>>Campaign Created</option>
                <option value="Chat Opened" <?= ($actionFilter ?? '') === 'Chat Opened' ? 'selected' : '' ?>>Chat Opened</option>
                <option value="User Marked Completed" <?= ($actionFilter ?? '') === 'User Marked Completed' ? 'selected' : '' ?>>User Marked Completed</option>
                <option value="Cleanup Completed" <?= ($actionFilter ?? '') === 'Cleanup Completed' ? 'selected' : '' ?>>Cleanup Completed</option>
            </select>
        </div>

        <div style="width: 200px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Status</label>
            <select name="status" style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                <option value="">All Statuses</option>
                <option value="Success" <?= ($statusFilter ?? '') === 'Success' ? 'selected' : '' ?>>Success</option>
                <option value="Failed" <?= ($statusFilter ?? '') === 'Failed' ? 'selected' : '' ?>>Failed</option>
                <option value="Blocked" <?= ($statusFilter ?? '') === 'Blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem;">Filter</button>
    </form>
</div>

<!-- Activity Logs Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            Activity Logs (<?= count($activities) ?>)
        </h3>
        <span class="badge badge-neutral">Immutable Audit Trail</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Status</th>
                    <th>Details / Notes</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No matching logs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($activities as $a): ?>
                        <tr>
                            <td><strong><?= e($a->action_type) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= $a->status === 'Success' ? 'success' : ($a->status === 'Blocked' ? 'danger' : 'warning') ?>">
                                    <?= e($a->status) ?>
                                </span>
                            </td>
                            <td><span style="font-size: 0.85rem; color: var(--text-secondary);"><?= e($a->notes ?? '&mdash;') ?></span></td>
                            <td><code><?= e($a->ip_address) ?></code></td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y H:i:s', strtotime($a->created_at)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
