<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading);"><?= e($campaign->name) ?></h2>
            <span class="badge badge-<?= $campaign->status === 'in_progress' ? 'success' : ($campaign->status === 'completed' || $campaign->status === 'cleaned_up' ? 'info' : 'warning') ?>" style="font-size: 0.8rem;">
                <?= strtoupper(str_replace('_', ' ', $campaign->status)) ?>
            </span>
        </div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            List: <strong><?= e($list->name ?? 'Default List') ?></strong> &bull; Draft: <strong><?= e($draft->title ?? 'Default Template') ?></strong>
            <?php if (!empty($draft->has_attachment)): ?>
                <span class="badge badge-success" style="margin-left: 0.5rem; font-size: 0.75rem;">📷 Image Included</span>
            <?php endif; ?>
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <?php if ($stats['pending'] > 0 || $stats['chat_opened'] > 0):
            $pendingCount = $stats['pending'] + $stats['chat_opened'];
        ?>
            <!-- Live Gateway Status Indicator -->
            <div id="gatewayStatusBadge" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.45rem 0.85rem; border-radius: var(--radius-md); font-size: 0.8rem; background: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); color: var(--text-secondary);">
                <span class="status-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span>
                <span id="gatewayStatusText">Checking Gateway...</span>
            </div>

            <!-- Direct Automated Broadcast Button -->
            <button type="button" id="btnDirectSend" onclick="triggerDirectSend()" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-weight: 700; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);">
                <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span id="btnDirectSendText">⚡ Start Direct Automated Send <?= !empty($draft->has_attachment) ? '(with Image)' : '' ?> (<?= $pendingCount ?> Pending)</span>
            </button>

            <!-- Hidden Fallback Form for Direct Submission -->
            <form id="directSendFallbackForm" method="POST" action="/campaigns/<?= (int)$campaign->id ?>/direct-send" style="display:none;">
                <?= csrf_field() ?>
            </form>

            <!-- Refresh Messages Button -->
            <form method="POST" action="/campaigns/<?= e($campaign->id) ?>/refresh-messages" style="display:inline;"
                  onsubmit="return confirm('Re-generate messages from latest draft?\n\nThis will update <?= $pendingCount ?> pending recipient message(s) with the current draft content.\nUse this after editing the draft (e.g. adding a link).')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-secondary" style="color: #a78bfa; border-color: rgba(167,139,250,0.4); background: rgba(167,139,250,0.08);" title="Re-sync messages from latest draft content">
                    🔄 Refresh Messages from Draft
                </button>
            </form>
        <?php endif; ?>


        <?php if ($campaign->status === 'ready_for_review' || $campaign->status === 'draft'): ?>
            <a href="/campaigns/<?= e($campaign->id) ?>/start" class="btn btn-whatsapp">
                Manual Web Assistant &rarr;
            </a>
        <?php elseif ($campaign->status === 'in_progress'): ?>
            <a href="/assistant/<?= e($campaign->id) ?>" class="btn btn-whatsapp">
                Continue Assistant &rarr;
            </a>
            <a href="/campaigns/<?= e($campaign->id) ?>/pause" class="btn btn-secondary">
                Pause
            </a>
        <?php elseif ($campaign->status === 'paused'): ?>
            <a href="/campaigns/<?= e($campaign->id) ?>/start" class="btn btn-whatsapp">
                Resume Assistant &rarr;
            </a>
        <?php endif; ?>
        <a href="/campaigns" class="btn btn-secondary">&larr; Back to Campaigns</a>
        <form method="POST" action="/campaigns/<?= e($campaign->id) ?>/delete" style="display:inline;" onsubmit="return confirm('Delete campaign \"<?= addslashes(e($campaign->name)) ?>\"?\n\nThis will permanently remove the campaign and ALL its recipients.\nThis action CANNOT be undone.')">
            <?= csrf_field() ?>
            <button type="submit" class="btn" style="background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.35); cursor: pointer;" title="Delete this campaign permanently">
                🗑 Delete Campaign
            </button>
        </form>
    </div>
</div>

<!-- Direct Send Progress Banner (Hidden by default) -->
<div id="directSendBanner" style="display: none; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.1) 100%); border: 1px solid var(--accent-emerald); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <div style="font-weight: 700; color: var(--accent-emerald); display: flex; align-items: center; gap: 0.5rem;">
            <svg class="animate-spin" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span id="directSendStatusText">Direct Automated Sending in Progress...</span>
        </div>
        <span id="directSendPercent" class="badge badge-success">Processing</span>
    </div>
    <div style="width: 100%; height: 8px; background: rgba(0,0,0,0.3); border-radius: 4px; overflow: hidden;">
        <div id="directSendProgressBar" style="width: 0%; height: 100%; background: var(--accent-emerald); transition: width 0.4s ease;"></div>
    </div>
</div>

<!-- Campaign Summary Cards -->
<div class="grid-cols-4">
    <div class="card stat-card">
        <div>
            <div class="stat-label">Total Recipients</div>
            <div class="stat-value" style="color: var(--text-primary);"><?= e($stats['total']) ?></div>
            <div class="stat-label">Enrolled in Cohort</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">User Confirmed</div>
            <div class="stat-value" style="color: var(--accent-wa);"><?= e($stats['completed']) ?></div>
            <div class="stat-label">Manually Dispatched</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-green">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Pending / In Progress</div>
            <div class="stat-value" style="color: #60a5fa;"><?= e($stats['pending'] + $stats['chat_opened']) ?></div>
            <div class="stat-label">Awaiting User Action</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
    </div>

    <div class="card stat-card">
        <div>
            <div class="stat-label">Skipped / Excluded</div>
            <div class="stat-value" style="color: var(--accent-rose);"><?= e($stats['skipped'] + $stats['opted_out'] + $stats['invalid']) ?></div>
            <div class="stat-label">Opted-Out or Suppressed</div>
        </div>
        <div class="stat-icon-wrapper stat-icon-rose">
            <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
        </div>
    </div>
</div>

<!-- Recipients Detailed Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recipient Queue & Outcome Log (<?= count($recipients) ?>)</h3>
        <span class="badge badge-neutral">User-Controlled Confirmations Only</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Contact ID</th>
                    <th>Recipient Status</th>
                    <th>Prepared Message Preview</th>
                    <th>Notes</th>
                    <th>Completion Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recipients as $idx => $r): ?>
                    <?php 
                        $statusClass = match($r->status) {
                            'user_marked_completed' => 'badge-success',
                            'chat_opened' => 'badge-info',
                            'skipped' => 'badge-warning',
                            'opted_out', 'invalid' => 'badge-danger',
                            default => 'badge-neutral',
                        };
                    ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><code>Contact #<?= e($r->contact_id) ?></code></td>
                        <td>
                            <span class="badge <?= $statusClass ?>">
                                <?= strtoupper(str_replace('_', ' ', $r->status)) ?>
                            </span>
                        </td>
                        <td style="max-width: 320px;">
                            <div style="font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= e($r->prepared_message) ?>
                            </div>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);"><?= e($r->action_notes ?? '&mdash;') ?></td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);"><?= $r->completed_at ? date('M j, H:i', strtotime($r->completed_at)) : '&mdash;' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
let isGatewayConnected = false;

// Check live WhatsApp gateway status on page load
async function checkGatewayStatus() {
    const badge = document.getElementById('gatewayStatusBadge');
    const text = document.getElementById('gatewayStatusText');
    const dot = badge ? badge.querySelector('.status-dot') : null;

    try {
        const res = await fetch('/gateway/status', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json().catch(() => ({}));

        if (data && data.status === 'connected') {
            isGatewayConnected = true;
            if (dot) dot.style.background = '#10b981';
            const userPhone = data.user?.id ? (' (+' + data.user.id.split(':')[0].replace(/\D/g, '') + ')') : '';
            if (text) text.innerHTML = '<span style="color: #34d399; font-weight: 600;">● Gateway Ready' + userPhone + '</span>';
            if (badge) {
                badge.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                badge.style.background = 'rgba(16, 185, 129, 0.08)';
            }
        } else {
            isGatewayConnected = false;
            if (dot) dot.style.background = '#f59e0b';
            if (text) text.innerHTML = '<a href="/gateway" style="color: #fbbf24; text-decoration: underline; font-weight: 600;">⚠️ Device Not Linked (Link QR)</a>';
            if (badge) {
                badge.style.borderColor = 'rgba(245, 158, 11, 0.4)';
                badge.style.background = 'rgba(245, 158, 11, 0.08)';
            }
        }
    } catch (e) {
        if (text) text.innerText = 'Gateway Status Unknown';
    }
}

document.addEventListener('DOMContentLoaded', checkGatewayStatus);

function triggerDirectSend() {
    if (!isGatewayConnected) {
        if (confirm('WhatsApp Device does not appear to be linked yet.\n\nWould you like to open the WhatsApp QR Gateway page to link your phone now?')) {
            window.location.href = '/gateway';
            return;
        }
    }

    if (!confirm('Start Direct Automated Sending for all pending recipients in this campaign now?')) {
        return;
    }

    const btn = document.getElementById('btnDirectSend');
    const btnText = document.getElementById('btnDirectSendText');
    const banner = document.getElementById('directSendBanner');
    const statusText = document.getElementById('directSendStatusText');
    const bar = document.getElementById('directSendProgressBar');
    const percentBadge = document.getElementById('directSendPercent');
    const fallbackForm = document.getElementById('directSendFallbackForm');

    if (btn) btn.disabled = true;
    if (btnText) btnText.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;margin-right:6px;vertical-align:middle;"></span> Dispatching to WhatsApp...';
    if (banner) banner.style.display = 'block';
    if (bar) bar.style.width = '35%';
    if (statusText) statusText.innerText = 'Dispatching messages via Direct Automated Gateway...';
    if (percentBadge) {
        percentBadge.className = 'badge badge-warning';
        percentBadge.innerText = 'Sending...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/campaigns/<?= (int)$campaign->id ?>/direct-send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            _csrf_token: csrfToken
        })
    }).then(async response => {
        const res = await response.json().catch(() => ({}));
        if (bar) bar.style.width = '100%';

        if (response.ok && res && res.success && (res.sent_count > 0 || res.completed)) {
            if (statusText) statusText.innerText = 'Success! ' + res.sent_count + ' message(s) delivered. Reloading...';
            if (percentBadge) {
                percentBadge.className = 'badge badge-success';
                percentBadge.innerText = 'Completed';
            }
            if (window.Toast) window.Toast.show('success', 'Direct automated dispatch complete! ' + res.sent_count + ' message(s) delivered.');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            const errorMsg = (res && res.error) || (res && res.message) || 'Failed to dispatch messages.';
            if (statusText) statusText.innerText = 'Error: ' + errorMsg;
            if (percentBadge) {
                percentBadge.className = 'badge badge-danger';
                percentBadge.innerText = 'Failed';
            }
            if (window.Toast) window.Toast.show('error', errorMsg);
            if (btn) {
                btn.disabled = false;
                if (btnText) btnText.innerHTML = '⚡ Retry Direct Automated Send';
            }

            // If user wants to try standard server form submission as fallback
            if (confirm('Direct automated dispatch encountered an error:\n\n' + errorMsg + '\n\nTry fallback direct server dispatch?')) {
                if (fallbackForm) fallbackForm.submit();
            }
        }
    }).catch(err => {
        const errorMsg = err.message || 'Network connection failed.';
        if (statusText) statusText.innerText = 'Network Error: ' + errorMsg;
        if (btn) {
            btn.disabled = false;
            if (btnText) btnText.innerHTML = '⚡ Retry Direct Automated Send';
        }
        if (window.Toast) window.Toast.show('error', 'Network error: ' + errorMsg);
        if (confirm('Network error during request: ' + errorMsg + '\n\nSubmit via fallback form?')) {
            if (fallbackForm) fallbackForm.submit();
        }
    });
}
</script>

