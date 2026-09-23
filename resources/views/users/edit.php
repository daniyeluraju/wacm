<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Edit User: <?= e($user->name) ?></h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Modify permissions, update credentials, or adjust account status.</p>
    </div>
    <div>
        <a href="/users" class="btn btn-secondary">&larr; Back to Users</a>
    </div>
</div>

<div class="card" style="max-width: 650px;">
    <form method="POST" action="/users/<?= e($user->id) ?>">
        <?= csrf_field() ?>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Full Name</label>
            <input type="text" name="name" value="<?= e(old('name', $user->name)) ?>" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Email Address</label>
            <input type="email" name="email" value="<?= e(old('email', $user->email)) ?>" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Role Assignment</label>
            <select name="role" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                <option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>>Admin (Full contact, campaign & assistant access)</option>
                <option value="viewer" <?= $user->role === 'viewer' ? 'selected' : '' ?>>Viewer (Read-only access)</option>
                <option value="super_admin" <?= $user->role === 'super_admin' ? 'selected' : '' ?>>Super Admin (Full system, user & audit access)</option>
            </select>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Account Status</label>
            <select name="status" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>Active (Enabled)</option>
                <option value="suspended" <?= $user->status === 'suspended' ? 'selected' : '' ?>>Suspended (Access blocked)</option>
                <option value="inactive" <?= $user->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div style="margin-bottom: 1.75rem; padding: 1rem; background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.25rem;">Reset Password (Optional)</label>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">Leave empty if you do not wish to change this user's password.</p>
            <input type="password" name="new_password" placeholder="Enter new password (min 8 chars)" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                Save Changes
            </button>
            <a href="/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
