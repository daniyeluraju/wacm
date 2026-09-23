<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Contact Directory</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Organize phone numbers, monitor recorded consent, and manage opt-out suppressions.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="/contacts/export" class="btn btn-secondary">
            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </a>
        <a href="/import" class="btn btn-secondary">
            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
            Import Wizard
        </a>
        <a href="/contacts/create" class="btn btn-primary">
            <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add Contact
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="/contacts" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Search</label>
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Name, phone, or email..." style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
        </div>

        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Group</label>
            <select name="group" style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                <option value="">All Groups</option>
                <?php foreach ($groups as $g): ?>
                    <option value="<?= e($g) ?>" <?= ($filters['group'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Consent</label>
            <select name="consent_status" style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                <option value="">All Statuses</option>
                <option value="granted" <?= ($filters['consent_status'] ?? '') === 'granted' ? 'selected' : '' ?>>Granted</option>
                <option value="pending" <?= ($filters['consent_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="denied" <?= ($filters['consent_status'] ?? '') === 'denied' ? 'selected' : '' ?>>Denied</option>
                <option value="unspecified" <?= ($filters['consent_status'] ?? '') === 'unspecified' ? 'selected' : '' ?>>Unspecified</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Opt-Out Status</label>
            <select name="opt_out" style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                <option value="">All</option>
                <option value="0" <?= ($filters['opt_out'] ?? '') === '0' ? 'selected' : '' ?>>Active (Eligible)</option>
                <option value="1" <?= ($filters['opt_out'] ?? '') === '1' ? 'selected' : '' ?>>Opted-Out (Suppressed)</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem;">Filter</button>
        </div>
    </form>
</div>

<!-- Contacts Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Contacts (<?= count($contacts) ?>)</h3>
        <span class="badge badge-neutral">E.164 Standardized</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Normalized Phone</th>
                    <th>Group</th>
                    <th>Consent</th>
                    <th>Opt-Out</th>
                    <th>Created</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No contacts found matching your query. <a href="/contacts/create">Add your first contact</a> or <a href="/import">run CSV import</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);"><?= e($c->full_name) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($c->email ?? 'No email') ?></div>
                            </td>
                            <td>
                                <code style="color: var(--accent-wa); font-weight: 600;"><?= e($c->phone_normalized) ?></code>
                                <div style="font-size: 0.7rem; color: var(--text-muted);"><?= e($c->phone_raw) ?> (<?= e($c->country_code) ?>)</div>
                            </td>
                            <td>
                                <?= $c->group_name ? '<span class="badge badge-info">' . e($c->group_name) . '</span>' : '<span style="color: var(--text-muted);">&mdash;</span>' ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $c->consent_status === 'granted' ? 'success' : ($c->consent_status === 'denied' ? 'danger' : 'neutral') ?>">
                                    <?= ucfirst(e($c->consent_status)) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($c->opt_out_status): ?>
                                    <span class="badge badge-danger">Opted Out</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><span class="badge-dot"></span> Eligible</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($c->created_at)) ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.5rem;">
                                    <a href="/contacts/<?= e($c->id) ?>/edit" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">Edit</a>
                                    <form method="POST" action="/contacts/<?= e($c->id) ?>/delete" onsubmit="return confirm('Remove contact <?= addslashes(e($c->full_name)) ?>?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
