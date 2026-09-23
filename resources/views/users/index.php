<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">User Management</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Manage team accounts, assign roles, and monitor authentication status.</p>
    </div>
    <div>
        <a href="/users/create" class="btn btn-primary">
            <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add New User
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            System Users (<?= count($users) ?>)
        </h3>
        <span class="badge badge-info">Role-Based Access Control</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php 
                        $roleBadge = match($u->role) {
                            'super_admin' => 'badge-danger',
                            'admin' => 'badge-info',
                            default => 'badge-neutral',
                        };
                        $statusBadge = $u->status === 'active' ? 'badge-success' : 'badge-danger';
                        $currentUser = session('user');
                        $isSelf = (int)$currentUser['id'] === (int)$u->id;
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-indigo), var(--accent-primary)); color: #fff; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;">
                                    <?= strtoupper(substr($u->name, 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?= e($u->name) ?></div>
                                    <?php if ($isSelf): ?>
                                        <span style="font-size: 0.65rem; color: var(--accent-wa); font-weight: 600;">(Your Account)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><code><?= e($u->email) ?></code></td>
                        <td>
                            <span class="badge <?= $roleBadge ?>">
                                <?= strtoupper(str_replace('_', ' ', $u->role)) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $statusBadge ?>">
                                <span class="badge-dot"></span>
                                <?= ucfirst(e($u->status)) ?>
                            </span>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);">
                            <?= $u->last_login_at ? e($u->last_login_at) : 'Never' ?>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($u->created_at)) ?></td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.5rem;">
                                <a href="/users/<?= e($u->id) ?>/edit" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">
                                    Edit
                                </a>
                                <?php if (!$isSelf): ?>
                                    <form method="POST" action="/users/<?= e($u->id) ?>/delete" onsubmit="return confirm('Are you sure you want to delete user <?= addslashes(e($u->name)) ?>?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
