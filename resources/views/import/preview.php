<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Step 2: Map Columns & Confirm Import</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">File: <code><?= e($fileName) ?></code> &bull; Detected <?= e($preview['total_rows']) ?> records</p>
    </div>
    <div>
        <a href="/import" class="btn btn-secondary">&larr; Cancel & Re-upload</a>
    </div>
</div>

<form method="POST" action="/import/process">
    <?= csrf_field() ?>
    <input type="hidden" name="delimiter" value="<?= e($preview['delimiter']) ?>">

    <!-- Important Mapping Notice -->
    <div style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 1rem;">
        <svg style="width: 24px; height: 24px; color: var(--accent-primary); flex-shrink: 0; margin-top: 2px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
            <strong style="color: var(--text-primary);">Required Column:</strong> Ensure that you map the <strong>Phone Number (Raw)</strong> field below to the column in your CSV containing the phone numbers. If a column is omitted or left as "Do Not Import", rows without phone numbers will be rejected.
        </div>
    </div>

    <!-- Country Code Fallback Setting -->
    <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); display: block; margin-bottom: 0.2rem;">Default Country Code (for numbers without country prefix)</label>
                <p style="font-size: 0.75rem; color: var(--text-muted);">If your CSV phone numbers do not include leading country codes, select your country code to automatically attach it.</p>
            </div>
            <select name="default_country_code" style="width: 220px; padding: 0.65rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                <option value="+1">+1 (US / Canada)</option>
                <option value="+91" selected>+91 (India)</option>
                <option value="+44">+44 (UK)</option>
                <option value="+61">+61 (Australia)</option>
                <option value="+971">+971 (UAE)</option>
                <option value="+63">+63 (Philippines)</option>
                <option value="+49">+49 (Germany)</option>
                <option value="+33">+33 (France)</option>
                <option value="+81">+81 (Japan)</option>
                <option value="+55">+55 (Brazil)</option>
                <option value="+27">+27 (South Africa)</option>
            </select>
        </div>
    </div>

    <!-- Column Mapping Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                Map File Columns to Database Fields
            </h3>
        </div>

        <?php
        $dbFields = [
            'phone_raw' => ['label' => 'Phone Number (Required)', 'required' => true, 'keywords' => ['phone', 'mobile', 'cell', 'tel', 'contact_no', 'whatsapp', 'number']],
            'full_name' => ['label' => 'Full Name', 'required' => false, 'keywords' => ['name', 'full_name', 'contact', 'client', 'customer', 'person', 'lead', 'first_name']],
            'country_code' => ['label' => 'Country Code (Optional)', 'required' => false, 'keywords' => ['country_code', 'country', 'code', 'prefix']],
            'group_name' => ['label' => 'Group / Tag Name', 'required' => false, 'keywords' => ['group', 'tag', 'category', 'list', 'segment', 'cohort']],
            'email' => ['label' => 'Email Address', 'required' => false, 'keywords' => ['email', 'mail', 'email_address']],
            'consent_status' => ['label' => 'Consent Status', 'required' => false, 'keywords' => ['consent', 'permission', 'opt_in', 'consent_status']],
            'consent_source' => ['label' => 'Consent Source', 'required' => false, 'keywords' => ['source', 'origin', 'consent_source']],
            'custom_field_1' => ['label' => 'Custom Field 1', 'required' => false, 'keywords' => ['custom_1', 'custom1', 'field_1', 'custom']],
            'custom_field_2' => ['label' => 'Custom Field 2', 'required' => false, 'keywords' => ['custom_2', 'custom2', 'field_2']],
            'notes' => ['label' => 'Notes', 'required' => false, 'keywords' => ['note', 'notes', 'comment', 'description', 'remarks']],
        ];
        ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
            <?php foreach ($dbFields as $fieldKey => $info): ?>
                <div style="display: flex; flex-direction: column; gap: 0.35rem; padding: 0.85rem; background-color: var(--bg-surface-elevated); border: 1px solid <?= $info['required'] ? 'rgba(59, 130, 246, 0.4)' : 'var(--border-subtle)' ?>; border-radius: var(--radius-md);">
                    <label style="font-size: 0.8rem; font-weight: 700; color: <?= $info['required'] ? '#60a5fa' : 'var(--text-secondary)' ?>;">
                        <?= e($info['label']) ?> <?= $info['required'] ? '<span style="color: var(--accent-rose); font-size: 1rem;">*</span>' : '' ?>
                    </label>
                    <select name="mapping[<?= e($fieldKey) ?>]" style="padding: 0.65rem 0.85rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                        <option value="">-- Do Not Import / Ignore --</option>
                        <?php foreach ($preview['headers'] as $idx => $hdr): ?>
                            <?php 
                                $normHdr = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string)$hdr)));
                                $isMatch = false;
                                foreach ($info['keywords'] as $kw) {
                                    $normKw = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $kw)));
                                    if (str_contains($normHdr, $normKw) || str_contains($normKw, $normHdr)) {
                                        $isMatch = true;
                                        break;
                                    }
                                }
                            ?>
                            <option value="<?= $idx ?>" <?= $isMatch ? 'selected' : '' ?>>
                                Column <?= $idx + 1 ?>: "<?= e($hdr) ?>" <?= $isMatch ? '(Auto-Detected)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Preview Table -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Sample Data Preview (First 5 Rows)</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <?php foreach ($preview['headers'] as $hdr): ?>
                            <th><?= e($hdr) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preview['preview_rows'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $val): ?>
                                <td><?= e($val) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
        <a href="/import" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
            Confirm & Run Batch Import &rarr;
        </button>
    </div>
</form>
