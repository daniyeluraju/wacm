<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">CSV & XLSX Import Wizard</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Batch import phone numbers, detect duplicates, and standardize international formats.</p>
    </div>
    <div>
        <a href="/import/sample" class="btn btn-secondary">
            <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Download Sample CSV Template
        </a>
    </div>
</div>

<!-- Last Import Rejections Banner (If any) -->
<?php if (!empty($lastRejections) && !empty($lastRejections['rejected_rows'])): ?>
    <div class="card" style="border-left: 4px solid var(--accent-rose); margin-bottom: 2rem; background-color: rgba(244, 63, 94, 0.05);">
        <div class="card-header" style="border-bottom-color: rgba(244, 63, 94, 0.2);">
            <h3 class="card-title" style="color: var(--accent-rose);">
                <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Import Rejection Report &mdash; Why Records Were Rejected (<?= count($lastRejections['rejected_rows']) ?> rows)
            </h3>
            <?php if (!empty($lastRejections['error_file'])): ?>
                <a href="/import/errors/<?= e($lastRejections['error_file']) ?>" class="btn btn-danger" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                    Download Error CSV Report &darr;
                </a>
            <?php endif; ?>
        </div>

        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
            The following records could not be imported. Review the reasons below to fix your file or adjust column mappings in Step 2:
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Row #</th>
                        <th>Name</th>
                        <th>Phone Provided</th>
                        <th>Rejection Reason</th>
                        <th>How to Resolve</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lastRejections['rejected_rows'] as $rej): ?>
                        <tr>
                            <td><code>Row <?= e($rej['row']) ?></code></td>
                            <td><?= e($rej['name']) ?></td>
                            <td><code style="color: #fb7185;"><?= e($rej['phone']) ?></code></td>
                            <td style="color: #fb7185; font-weight: 500;"><?= e($rej['reason']) ?></td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">
                                <?php if (str_contains($rej['reason'], 'Missing phone')): ?>
                                    Ensure the <strong>Phone Number (Raw)</strong> field in Step 2 is mapped to your phone column.
                                <?php else: ?>
                                    Verify country code and ensure number has 7 to 15 standard digits.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="grid-cols-2">
    <!-- Upload Box -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Step 1: Upload Contact File
            </h3>
            <span class="badge badge-info">CSV Supported</span>
        </div>

        <form method="POST" action="/import/upload" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div style="border: 2px dashed var(--border-medium); border-radius: var(--radius-lg); padding: 2.5rem 1.5rem; text-align: center; margin-bottom: 1.5rem; background-color: var(--bg-input);">
                <svg style="width: 40px; height: 40px; color: var(--accent-primary); margin-bottom: 0.75rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Select CSV File to Upload</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.25rem;">Max 10MB &bull; Automatic header detection</div>
                <input type="file" name="import_file" accept=".csv,.txt" required style="font-size: 0.85rem; color: var(--text-secondary);">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Optionally Assign to Contact List</label>
                <select name="list_id" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
                    <option value="">None (General Directory)</option>
                    <?php foreach ($lists as $l): ?>
                        <option value="<?= e($l->id) ?>"><?= e($l->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem;">
                Upload & Preview Columns &rarr;
            </button>
        </form>
    </div>

    <!-- Instructions & Quick Guide -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                How to Ensure 100% Import Success
            </h3>
        </div>

        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
            <li style="display: flex; gap: 0.5rem;">
                <span style="color: var(--accent-wa); font-weight: bold;">1.</span>
                <span><strong>Include Phone Numbers:</strong> Every contact requires a valid phone number. In Step 2, ensure the <em>Phone Number (Raw)</em> dropdown is mapped to your phone column.</span>
            </li>
            <li style="display: flex; gap: 0.5rem;">
                <span style="color: var(--accent-wa); font-weight: bold;">2.</span>
                <span><strong>Country Codes:</strong> If your numbers are 10 digits (e.g. <code>9876543210</code>), select your Default Country Code (e.g. <code>+91</code> or <code>+1</code>) in Step 2.</span>
            </li>
            <li style="display: flex; gap: 0.5rem;">
                <span style="color: var(--accent-wa); font-weight: bold;">3.</span>
                <span><strong>Duplicate Protection:</strong> Contacts already in your directory with identical normalized numbers are automatically preserved without duplicate duplicates.</span>
            </li>
            <li style="display: flex; gap: 0.5rem;">
                <span style="color: var(--accent-wa); font-weight: bold;">4.</span>
                <span><strong>Quick Start:</strong> Download the <a href="/import/sample" style="color: #38bdf8;">Sample CSV Template</a> for pre-configured headers.</span>
            </li>
        </ul>
    </div>
</div>

<!-- Import History Table -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Recent Import History</h3>
        <span class="badge badge-neutral">Audit Logged</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>Total Rows</th>
                    <th>Imported</th>
                    <th>Rejected</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Report</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No import history logged yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><code><?= e($h->file_name) ?></code></td>
                            <td><?= format_bytes((int)$h->file_size) ?></td>
                            <td><?= e($h->total_rows) ?></td>
                            <td><span class="badge badge-success"><?= e($h->imported_rows) ?></span></td>
                            <td>
                                <span class="badge badge-<?= $h->rejected_rows > 0 ? 'danger' : 'neutral' ?>">
                                    <?= e($h->rejected_rows) ?>
                                </span>
                            </td>
                            <td><span class="badge badge-success"><?= ucfirst(e($h->status)) ?></span></td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y H:i', strtotime($h->created_at)) ?></td>
                            <td style="text-align: right;">
                                <?php if (!empty($h->error_log_path)): ?>
                                    <a href="/import/errors/<?= e($h->error_log_path) ?>" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; color: #fb7185;">
                                        Download Rejection Log
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">None</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
