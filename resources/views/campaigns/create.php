<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Create New Campaign</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Select a recipient list, associate a message draft, and configure user pacing reminder intervals.</p>
    </div>
    <div>
        <a href="/campaigns" class="btn btn-secondary">&larr; Back to Campaigns</a>
    </div>
</div>

<div class="card" style="max-width: 750px;">
    <form method="POST" action="/campaigns">
        <?= csrf_field() ?>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Campaign Name *</label>
            <input type="text" name="name" value="<?= e(old('name')) ?>" required placeholder="e.g. VIP Member Welcome Wave 1" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Campaign Objective / Notes</label>
            <textarea name="description" rows="2" placeholder="Optional notes for internal tracking..." style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none; resize: vertical;"><?= e(old('description')) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Target Audience / Contact List *</label>
                <select name="contact_list_id" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                    <option value="all" selected>All Active Contacts (<?= (int)($activeContactCount ?? 0) ?> Contacts in Directory)</option>
                    <?php if (!empty($lists)): ?>
                        <optgroup label="Specific Contact Lists">
                            <?php foreach ($lists as $l): ?>
                                <option value="<?= e($l->id) ?>"><?= e($l->name) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Select Message Draft *</label>
                <select name="message_draft_id" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                    <?php foreach ($drafts as $d): ?>
                        <option value="<?= e($d->id) ?>"><?= e($d->title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Pacing Reminder Configuration -->
        <div style="margin-bottom: 1.5rem; padding: 1.25rem; background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">User Pacing Reminder Interval</label>
                <span class="badge badge-neutral">User Guide Only</span>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem;">
                Configures the timer interval shown to the operator between manual chats to encourage natural pacing.
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.25rem;">Minimum Reminder (Seconds)</label>
                    <input type="number" name="pacing_min_seconds" value="32" min="10" max="120" style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.25rem;">Maximum Reminder (Seconds)</label>
                    <input type="number" name="pacing_max_seconds" value="40" min="10" max="120" style="width: 100%; padding: 0.6rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                Create Campaign & Preview Recipients &rarr;
            </button>
            <a href="/campaigns" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
