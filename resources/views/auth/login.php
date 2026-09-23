<form method="POST" action="/login">
    <?= csrf_field() ?>

    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.35rem; font-family: var(--font-heading);">Sign In</h3>
    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.75rem;">Access the WhatsApp Assistant & Contact Manager</p>

    <div style="margin-bottom: 1.25rem;">
        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Email Address</label>
        <input type="email" name="email" value="<?= e(old('email', 'admin@wacm.local')) ?>" required placeholder="user@wacm.local" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="this.style.borderColor='var(--border-medium)'">
    </div>

    <div style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">Password</label>
        </div>
        <input type="password" name="password" value="Admin@123456" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="this.style.borderColor='var(--border-medium)'">
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 600; margin-bottom: 1.5rem;">
        Sign In to WACM &rarr;
    </button>
</form>

<!-- Quick Credentials Card for Easy Testing -->
<div style="background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1rem; margin-top: 1rem;">
    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Development Credentials</div>
    <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.8rem;">
        <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
            <span>Super Admin:</span>
            <code style="color: #60a5fa; cursor: pointer;" onclick="document.querySelector('input[name=email]').value='admin@wacm.local'; document.querySelector('input[name=password]').value='Admin@123456';">admin@wacm.local</code>
        </div>
        <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
            <span>Admin:</span>
            <code style="color: #34d399; cursor: pointer;" onclick="document.querySelector('input[name=email]').value='manager@wacm.local'; document.querySelector('input[name=password]').value='Admin@123456';">manager@wacm.local</code>
        </div>
        <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
            <span>Viewer:</span>
            <code style="color: #cbd5e1; cursor: pointer;" onclick="document.querySelector('input[name=email]').value='viewer@wacm.local'; document.querySelector('input[name=password]').value='Viewer@123456';">viewer@wacm.local</code>
        </div>
    </div>
</div>
