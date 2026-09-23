<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Edit Contact: <?= e($contact->full_name) ?></h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Modify details, update consent status, or configure opt-out suppression.</p>
    </div>
    <div>
        <a href="/contacts" class="btn btn-secondary">&larr; Back to Contacts</a>
    </div>
</div>

<div class="card" style="max-width: 750px;">
    <form method="POST" action="/contacts/<?= e($contact->id) ?>">
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Full Name *</label>
                <input type="text" name="full_name" value="<?= e(old('full_name', $contact->full_name)) ?>" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Email Address</label>
                <input type="email" name="email" value="<?= e(old('email', $contact->email)) ?>" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 140px 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Country Code</label>
                <input type="text" name="country_code" value="<?= e(old('country_code', $contact->country_code)) ?>" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Phone Number *</label>
                <input type="text" name="phone_raw" value="<?= e(old('phone_raw', $contact->phone_raw)) ?>" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Group / Tag</label>
            <input type="text" name="group_name" value="<?= e(old('group_name', $contact->group_name)) ?>" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; padding: 1.25rem; background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Consent Status</label>
                <select name="consent_status" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                    <option value="granted" <?= $contact->consent_status === 'granted' ? 'selected' : '' ?>>Granted</option>
                    <option value="pending" <?= $contact->consent_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="unspecified" <?= $contact->consent_status === 'unspecified' ? 'selected' : '' ?>>Unspecified</option>
                    <option value="denied" <?= $contact->consent_status === 'denied' ? 'selected' : '' ?>>Denied</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Opt-Out Suppression</label>
                <select name="opt_out_status" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                    <option value="0" <?= !$contact->opt_out_status ? 'selected' : '' ?>>Active (Eligible for campaigns)</option>
                    <option value="1" <?= $contact->opt_out_status ? 'selected' : '' ?>>Opted-Out (Exclude from campaigns)</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Notes</label>
            <textarea name="notes" rows="3" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;"><?= e(old('notes', $contact->notes)) ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                Save Changes
            </button>
            <a href="/contacts" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
