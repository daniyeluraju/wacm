<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Create New User</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Add an operator or administrator with tailored permissions.</p>
    </div>
    <div>
        <a href="/users" class="btn btn-secondary">&larr; Back to Users</a>
    </div>
</div>

<div class="card" style="max-width: 650px;">
    <form method="POST" action="/users">
        <?= csrf_field() ?>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Full Name</label>
            <input type="text" name="name" value="<?= e(old('name')) ?>" required placeholder="e.g. Sarah Jenkins" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Email Address</label>
            <input type="email" name="email" value="<?= e(old('email')) ?>" required placeholder="sarah@wacm.local" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Role Assignment</label>
            <select name="role" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin (Full contact, campaign & assistant access)</option>
                <option value="viewer" <?= old('role') === 'viewer' ? 'selected' : '' ?>>Viewer (Read-only access)</option>
                <option value="super_admin" <?= old('role') === 'super_admin' ? 'selected' : '' ?>>Super Admin (Full system, user & audit access)</option>
            </select>
        </div>

        <div style="margin-bottom: 1.75rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Initial Password (min 8 characters)</label>
            <input type="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                Create User
            </button>
            <a href="/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
