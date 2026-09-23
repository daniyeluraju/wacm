<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Add New Contact</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Record contact details with verified consent and phone normalization.</p>
    </div>
    <div>
        <a href="/contacts" class="btn btn-secondary">&larr; Back to Contacts</a>
    </div>
</div>

<div class="card" style="max-width: 750px;">
    <form method="POST" action="/contacts">
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Full Name *</label>
                <input type="text" name="full_name" value="<?= e(old('full_name')) ?>" required placeholder="e.g. John Doe" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Email Address</label>
                <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="john@example.com" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 140px 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Country Code</label>
                <input type="text" name="country_code" value="<?= e(old('country_code', '+1')) ?>" placeholder="+1" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Phone Number *</label>
                <input type="text" name="phone_raw" value="<?= e(old('phone_raw')) ?>" required placeholder="e.g. (555) 234-5678 or 7911123456" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Group / Tag</label>
                <input type="text" name="group_name" value="<?= e(old('group_name')) ?>" placeholder="e.g. VIP Clients, Leads, Orientation" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Assign to Contact List</label>
                <select name="list_id" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                    <option value="">None (Individual contact)</option>
                    <?php foreach ($lists as $l): ?>
                        <option value="<?= e($l->id) ?>"><?= e($l->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; padding: 1.25rem; background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Consent Status</label>
                <select name="consent_status" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                    <option value="granted" selected>Granted (Explicit consent confirmed)</option>
                    <option value="pending">Pending (Pending authorization)</option>
                    <option value="unspecified">Unspecified</option>
                    <option value="denied">Denied</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Consent Source</label>
                <input type="text" name="consent_source" value="Direct Entry" placeholder="e.g. Website Signup, In-Store form" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Notes / Internal Reference</label>
            <textarea name="notes" rows="3" placeholder="Optional notes regarding this contact..." style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none; resize: vertical;"><?= e(old('notes')) ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                Save Contact
            </button>
            <a href="/contacts" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
