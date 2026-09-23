<?php
$user = auth_user() ?? [
    'name' => 'Guest User',
    'role' => 'viewer',
    'email' => 'guest@wacm.local'
];
?>
<header class="top-navbar">
    <div class="navbar-left">
        <button id="sidebar-toggle" style="display: none; background: none; border: 1px solid var(--border-medium); border-radius: var(--radius-sm); color: var(--text-primary); padding: 0.35rem 0.5rem; cursor: pointer;">
            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <h1 class="page-title"><?= e($pageTitle ?? 'WACM Console') ?></h1>
    </div>

    <div class="navbar-right">
        <!-- Safety Protocol Pill -->
        <div class="badge badge-success" title="WhatsApp Safe Mode: User-controlled assist only. No automated bots or DOM scrapers.">
            <span class="badge-dot"></span>
            <span>Compliance Mode Active</span>
        </div>

        <?php if (is_authenticated()): ?>
            <!-- User Profile & Quick Actions -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <a href="/profile" style="display: flex; align-items: center; gap: 0.75rem; background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-full); padding: 0.35rem 0.85rem 0.35rem 0.5rem; text-decoration: none; transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--border-medium)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-indigo), var(--accent-primary)); color: #fff; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;">
                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 0.8rem; font-weight: 600; line-height: 1.1; color: var(--text-primary);"><?= e($user['name'] ?? 'User') ?></span>
                        <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;"><?= e(str_replace('_', ' ', $user['role'] ?? 'viewer')) ?></span>
                    </div>
                </a>

                <a href="/logout" class="btn btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; border-radius: var(--radius-full);" title="Sign out of WACM">
                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    <span>Logout</span>
                </a>
            </div>
        <?php else: ?>
            <a href="/login" class="btn btn-primary" style="padding: 0.45rem 1rem; font-size: 0.85rem;">Sign In</a>
        <?php endif; ?>
    </div>
</header>
