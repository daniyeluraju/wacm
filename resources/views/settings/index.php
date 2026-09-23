<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">System Settings & Compliance Controls</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Configure user pacing guidelines, storage warning thresholds, and data retention rules.</p>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="/settings">
        <?= csrf_field() ?>

        <!-- Section 1: User Pacing Guide -->
        <div style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Operator Pacing Reminder Settings
            </h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Pacing intervals encourage a healthy, natural delay between manual message preparations.
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Default Minimum Interval (Seconds)</label>
                    <input type="number" name="pacing_reminder_min_seconds" value="<?= e($settings['pacing_reminder_min_seconds']) ?>" min="5" max="180" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Default Maximum Interval (Seconds)</label>
                    <input type="number" name="pacing_reminder_max_seconds" value="<?= e($settings['pacing_reminder_max_seconds']) ?>" min="5" max="180" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                </div>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border-subtle); margin-bottom: 2rem;">

        <!-- Section 2: Storage & Automatic Cleanup -->
        <div style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Storage & Data Retention Policies
            </h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Configure automated retention windows for temporary campaign spreadsheets and uploaded assets.
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Temporary File Retention Period</label>
                    <select name="cleanup_retention_hours" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                        <option value="0" <?= $settings['cleanup_retention_hours'] === '0' ? 'selected' : '' ?>>Immediate Cleanup (0 hours)</option>
                        <option value="24" <?= $settings['cleanup_retention_hours'] === '24' ? 'selected' : '' ?>>24 Hours (Default)</option>
                        <option value="168" <?= $settings['cleanup_retention_hours'] === '168' ? 'selected' : '' ?>>7 Days</option>
                        <option value="720" <?= $settings['cleanup_retention_hours'] === '720' ? 'selected' : '' ?>>30 Days</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Storage Warning Threshold (MB)</label>
                    <input type="number" name="storage_warning_threshold_mb" value="<?= e($settings['storage_warning_threshold_mb']) ?>" min="50" max="10000" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-size: 0.85rem; color: var(--text-primary);">
                    <input type="checkbox" name="cleanup_enabled" value="1" <?= $settings['cleanup_enabled'] ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                    <span>Enable Automatic Storage Cleanup subsystem</span>
                </label>

                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-size: 0.85rem; color: var(--text-primary);">
                    <input type="checkbox" name="cleanup_auto_delete_temp" value="1" <?= $settings['cleanup_auto_delete_temp'] ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                    <span>Auto-delete temporary CSV/XLSX import files immediately after processing</span>
                </label>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border-subtle); margin-bottom: 2rem;">

        <!-- Section 3: WhatsApp Direct Dispatch & Official Meta Cloud API -->
        <div style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Direct WhatsApp Dispatch & Meta Cloud API
            </h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Configure direct automated sending mode and official Meta WhatsApp Cloud API credentials.
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Dispatch Engine Mode</label>
                    <select name="whatsapp_api_mode" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                        <option value="direct_gateway" <?= ($settings['whatsapp_api_mode'] ?? '') === 'direct_gateway' ? 'selected' : '' ?>>⚡ Direct Automated Dispatch Gateway (Standard)</option>
                        <option value="meta_cloud_api" <?= ($settings['whatsapp_api_mode'] ?? '') === 'meta_cloud_api' ? 'selected' : '' ?>>🌐 Official Meta WhatsApp Cloud API (Meta Graph API)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Daily Message Quota Limit</label>
                    <input type="number" name="whatsapp_daily_limit" value="<?= e($settings['whatsapp_daily_limit'] ?? 1000) ?>" min="50" max="100000" required style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                </div>
            </div>

            <div style="background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 1rem;">
                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem;">
                    Meta Cloud API Credentials (Optional - For Official Meta Graph API)
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.35rem;">Phone Number ID</label>
                        <input type="text" name="meta_phone_number_id" value="<?= e($settings['meta_phone_number_id'] ?? '') ?>" placeholder="e.g. 102938475619283" style="width: 100%; padding: 0.65rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.35rem;">WhatsApp Business Account ID</label>
                        <input type="text" name="meta_business_account_id" value="<?= e($settings['meta_business_account_id'] ?? '') ?>" placeholder="e.g. 987654321012345" style="width: 100%; padding: 0.65rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.35rem;">Permanent Access Token (Bearer)</label>
                    <input type="password" name="meta_access_token" value="<?= e($settings['meta_access_token'] ?? '') ?>" placeholder="EAAG..." style="width: 100%; padding: 0.65rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem;">
                Save System Settings
            </button>
        </div>
    </form>
</div>
