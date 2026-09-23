<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">User Profile & Security</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Manage your administrator account credentials and view recent authentication logs.</p>
    </div>
    <div>
        <span class="badge badge-<?= $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin' ? 'info' : 'neutral') ?>" style="font-size: 0.85rem; padding: 0.4rem 0.85rem;">
            <?= strtoupper(str_replace('_', ' ', $user->role)) ?>
        </span>
    </div>
</div>

<div class="grid-cols-2">
    <!-- Edit Profile Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                Personal Details
            </h3>
        </div>

        <form method="POST" action="/profile">
            <?= csrf_field() ?>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Full Name</label>
                <input type="text" name="name" value="<?= e($user->name) ?>" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Email Address</label>
                <input type="email" name="email" value="<?= e($user->email) ?>" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Assigned Role</label>
                <input type="text" value="<?= strtoupper(str_replace('_', ' ', $user->role)) ?>" disabled style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); color: var(--text-muted); font-size: 0.9rem; cursor: not-allowed;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Save Profile Changes
            </button>
        </form>
    </div>

    <!-- Change Password Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-amber);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                Update Password
            </h3>
        </div>

        <form method="POST" action="/password/change">
            <?= csrf_field() ?>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Current Password</label>
                <input type="password" name="current_password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">New Password (min 8 characters)</label>
                <input type="password" name="new_password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <button type="submit" class="btn btn-secondary" style="width: 100%; border-color: var(--border-highlight);">
                Update Password
            </button>
        </form>
    </div>
</div>

<!-- Security Activity History -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">
            <svg style="width: 20px; height: 20px; color: var(--accent-emerald);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            Recent Security & Login Activity
        </h3>
        <span class="badge badge-neutral">IP Logged</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Status</th>
                    <th>IP Address</th>
                    <th>Details</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentActivities)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No activity logged yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentActivities as $act): ?>
                        <tr>
                            <td><strong><?= e($act->action_type) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= $act->status === 'Success' ? 'success' : ($act->status === 'Blocked' ? 'danger' : 'warning') ?>">
                                    <?= e($act->status) ?>
                                </span>
                            </td>
                            <td><code><?= e($act->ip_address) ?></code></td>
                            <td><span style="font-size: 0.8rem; color: var(--text-secondary);"><?= e($act->notes ?? 'N/A') ?></span></td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= e($act->created_at) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
