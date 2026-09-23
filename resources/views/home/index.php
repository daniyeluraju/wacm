<!-- Compliance & Safety Banner -->
<div class="compliance-banner">
    <div class="compliance-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
    </div>
    <div class="compliance-text">
        <h4>WhatsApp Compliance & Safety Guarantee</h4>
        <p>
            WACM operates strictly as a <strong>user-controlled productivity assistant</strong>. This platform enforces zero unofficial APIs, zero DOM scrapers, zero automated clicks, and never makes unverified delivery claims. All interactions require manual user confirmation.
        </p>
    </div>
</div>

<!-- Quick Stat Cards -->
<div class="grid-cols-4">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Database Status</div>
            <div class="stat-value" style="color: <?= $dbHealth['status'] ? 'var(--accent-emerald)' : 'var(--accent-rose)' ?>;">
                <?= $dbHealth['status'] ? 'Connected' : 'Offline' ?>
            </div>
            <div class="stat-label"><?= e($dbHealth['database'] ?? 'wacm') ?> &bull; <?= e($tableCount) ?> Tables</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-green">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">PHP Runtime</div>
            <div class="stat-value" style="color: #60a5fa;"><?= PHP_VERSION ?></div>
            <div class="stat-label">Apache / XAMPP Stack</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Active Users</div>
            <div class="stat-value" style="color: #c084fc;"><?= e($userCount) ?></div>
            <div class="stat-label">RBAC Roles Seeded</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-purple">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Current Phase</div>
            <div class="stat-value" style="color: #fbbf24;">Phase 1</div>
            <div class="stat-label">Foundation & Core MVC</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-amber">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
        </div>
    </div>
</div>

<!-- Diagnostics Grid -->
<div class="grid-cols-2">
    <!-- Database Schema Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                Migrated Database Tables (<?= count($tablesList) ?>)
            </h3>
            <span class="badge badge-success">utf8mb4</span>
        </div>
        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Table Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tablesList)): ?>
                        <tr><td colspan="3" style="text-align: center;">No tables found. Run <code>php database/migrate.php</code>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tablesList as $idx => $tableName): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><code><?= e($tableName) ?></code></td>
                                <td><span class="badge badge-success"><span class="badge-dot"></span> Ready</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Storage & Permissions Diagnostics -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: #60a5fa;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                Storage Directories & Permissions
            </h3>
            <span class="badge badge-info">Private Storage</span>
        </div>
        <div class="status-list">
            <?php foreach ($storageStatus as $key => $info): ?>
                <div class="status-item">
                    <div>
                        <div class="status-label">storage/<?= e($key) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);"><?= e($info['path']) ?></div>
                    </div>
                    <div>
                        <?php if ($info['exists'] && $info['writable']): ?>
                            <span class="badge badge-success"><span class="badge-dot"></span> Writable</span>
                        <?php elseif ($info['exists']): ?>
                            <span class="badge badge-warning"><span class="badge-dot"></span> Read-Only</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><span class="badge-dot"></span> Missing</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Seeded Accounts & Extension Matrix -->
<div class="grid-cols-2">
    <!-- Seeded Users -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: #c084fc;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Development Seeded Accounts (Phase 2 Ready)
            </h3>
            <span class="badge badge-neutral">Bcrypt Hashed</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Email Address</th>
                    <th>Default Password</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-danger">Super Admin</span></td>
                    <td><code>admin@wacm.local</code></td>
                    <td><code>Admin@123456</code></td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">Admin</span></td>
                    <td><code>manager@wacm.local</code></td>
                    <td><code>Admin@123456</code></td>
                </tr>
                <tr>
                    <td><span class="badge badge-neutral">Viewer</span></td>
                    <td><code>viewer@wacm.local</code></td>
                    <td><code>Viewer@123456</code></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- PHP Modules & Environment Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: #38bdf8;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                PHP Extensions & Security Controls
            </h3>
            <span class="badge badge-success">Verified</span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <?php foreach ($phpExtensions as $ext => $loaded): ?>
                <div class="status-item" style="padding: 0.5rem 0.85rem;">
                    <span style="font-size: 0.85rem; color: var(--text-primary);">ext-<?= e($ext) ?></span>
                    <?php if ($loaded): ?>
                        <span class="badge badge-success" style="font-size: 0.7rem;">Loaded</span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="font-size: 0.7rem;">Missing</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
