<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading);"><?= e($campaign->name) ?></h2>
            <span class="badge badge-success">Manual Assistant Active</span>
        </div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Follow the user-controlled steps below to open WhatsApp Web, review your message, and confirm manual dispatch.
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="/campaigns/<?= e($campaign->id) ?>" class="btn btn-secondary">&larr; Campaign Overview</a>
    </div>
</div>

<?php if (empty($sessionData)): ?>
    <!-- All Recipients Processed -->
    <div class="card" style="text-align: center; padding: 4rem 2rem;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(37, 211, 102, 0.15); color: var(--accent-wa); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg style="width: 40px; height: 40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
        <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; font-family: var(--font-heading); color: var(--text-primary);">Campaign Wave Completed!</h2>
        <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 2rem;">
            All eligible contacts in this campaign have been processed. The campaign has now transitioned to <code>Cleanup Pending</code> according to your data retention policy.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="/campaigns/<?= e($campaign->id) ?>" class="btn btn-secondary">View Final Summary</a>
            <a href="/storage" class="btn btn-primary">Review Cleanup Settings &rarr;</a>
        </div>
    </div>
<?php else: ?>
    <?php
        $rec = $sessionData['recipient'];
        $draft = $sessionData['draft'];
        $attachment = $sessionData['attachment'];
        $waUrl = $sessionData['wa_url'];
        $pacingSeconds = $sessionData['pacing_seconds'];
    ?>

    <!-- Workflow Steps Grid -->
    <div class="grid-cols-2">
        <!-- Contact & Message Preview Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Recipient: <?= e($rec['full_name']) ?>
                </h3>
                <span class="badge badge-info"><?= e($rec['group_name'] ?? 'General') ?></span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background-color: var(--bg-surface-elevated); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">PHONE (E.164)</div>
                        <div style="font-size: 1rem; font-weight: 700; color: var(--accent-wa); font-family: var(--font-mono);"><?= e($rec['phone_normalized']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">CONSENT STATUS</div>
                        <div style="margin-top: 2px;">
                            <span class="badge badge-<?= $rec['consent_status'] === 'granted' ? 'success' : 'warning' ?>">
                                <?= ucfirst(e($rec['consent_status'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Prepared Personalized Message -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">Prepared Personalized Message</label>
                        <button type="button" onclick="copyMessageText()" class="btn btn-secondary" style="padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                            Copy Message Text
                        </button>
                    </div>
                    <div id="preparedMessageText" style="background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); padding: 1rem; font-size: 0.9rem; color: #f8fafc; white-space: pre-wrap; line-height: 1.5; min-height: 100px;"><?= e($rec['prepared_message']) ?></div>
                </div>

                <?php if ($attachment): ?>
                    <div style="background-color: var(--bg-surface-elevated); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--accent-wa);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <span class="badge badge-success">📷 Attached Media</span>
                            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);"><?= e($attachment->original_name) ?></span>
                        </div>
                        <div style="max-height: 180px; overflow: hidden; border-radius: var(--radius-md); background: #000; text-align: center;">
                            <img src="/attachments/<?= (int)$attachment->id ?>" alt="Attached image preview" style="max-height: 180px; width: 100%; object-fit: contain; display: block; margin: 0 auto;">
                        </div>
                        <div style="font-size: 0.75rem; color: var(--accent-wa); margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            Direct Automated Gateway delivers this image + text together automatically!
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Manual Action & Pacing Reminder Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    Dispatch Steps &amp; Controls
                </h3>
                <span class="badge badge-success">⚡ Socket Gateway Ready</span>
            </div>

            <!-- 1-Click Direct Socket Send (With Image) -->
            <div style="margin-bottom: 1.25rem;">
                <button type="button" id="btnDirectSendSingle" onclick="dispatchDirectSingle()" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem; font-weight: 700; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                    <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    ⚡ Send Direct <?= $attachment ? '(with Image & Caption)' : '' ?> &amp; Next Contact (Spacebar)
                </button>
            </div>

            <!-- Auto-Pilot Runner Card -->
            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(5, 150, 105, 0.08) 100%); border: 1px solid var(--accent-emerald); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--accent-emerald); display: flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        Continuous Auto-Pilot Broadcast
                    </div>
                    <span id="autoPilotBadge" class="badge badge-neutral" style="font-size: 0.75rem;">Inactive</span>
                </div>
                
                <p style="font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 1rem; line-height: 1.4;">
                    Hands-free automatic sender: automatically delivers messages <?= $attachment ? '(with Image)' : '' ?> sequentially to everyone in the queue!
                </p>

                <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.75rem;">
                    <label style="font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap;">Auto-Next Delay:</label>
                    <select id="autoPilotDelaySelect" style="padding: 0.4rem 0.75rem; background: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.85rem; outline: none;">
                        <option value="3">3 Seconds (Ultra Fast)</option>
                        <option value="6" selected>6 Seconds (Recommended)</option>
                        <option value="10">10 Seconds</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" id="btnStartAutoPilot" onclick="toggleAutoPilot()" class="btn btn-whatsapp" style="flex: 1; padding: 0.85rem; font-size: 0.95rem; font-weight: 700; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);">
                        ▶ Start Hands-Free Auto-Pilot
                    </button>
                    <button type="button" id="btnStopAutoPilot" onclick="stopAutoPilot()" class="btn btn-secondary" style="display: none; padding: 0.85rem 1.25rem; font-size: 0.85rem;">
                        ⏹ Stop
                    </button>
                </div>

                <div id="autoPilotCountdownBox" style="display: none; margin-top: 1rem; background: rgba(0,0,0,0.3); border-radius: var(--radius-md); padding: 0.75rem 1rem; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Sending next in:</div>
                    <div id="autoPilotTimerDisplay" style="font-size: 1.75rem; font-weight: 800; color: #38bdf8; font-family: var(--font-heading);">
                        6s
                    </div>
                </div>
            </div>

            <!-- Manual Browser Link & Skip Controls -->
            <details style="background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.75rem 1rem;">
                <summary style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); cursor: pointer;">
                    Alternative / Skip Controls
                </summary>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.75rem;">
                    <a href="<?= e($waUrl) ?>" target="_blank" onclick="onOpenChat(<?= (int)$rec['id'] ?>)" class="btn btn-secondary" style="width: 100%; font-size: 0.85rem;">
                        Open Web WhatsApp Tab (Text Only) &rarr;
                    </a>

                    <form id="completeForm" method="POST" action="/assistant/recipient/<?= e($rec['id']) ?>/completed">
                        <?= csrf_field() ?>
                        <input type="hidden" name="campaign_id" value="<?= e($campaign->id) ?>">
                        <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 0.65rem; font-size: 0.85rem;">
                            &#10003; Mark Completed Manually
                        </button>
                    </form>

                    <form method="POST" action="/assistant/recipient/<?= e($rec['id']) ?>/skip" style="display: flex; gap: 0.5rem;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="campaign_id" value="<?= e($campaign->id) ?>">
                        <input type="text" name="reason" placeholder="Skip reason (e.g. invalid, opted out)..." style="flex: 1; padding: 0.45rem 0.75rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.8rem; outline: none;">
                        <button type="submit" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.8rem;">
                            Skip
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>

    <script>
    const waUrl = <?= json_encode($waUrl) ?>;
    const recipientId = <?= (int)$rec['id'] ?>;
    const campaignId = <?= (int)$campaign->id ?>;
    let autoPilotInterval = null;

    function copyMessageText() {
        const text = document.getElementById('preparedMessageText').innerText;
        window.copyToClipboard(text);
    }

    function onOpenChat(rId) {
        window.apiFetch('/assistant/recipient/' + rId + '/opened', { method: 'POST' });
    }

    async function dispatchDirectSingle() {
        const btn = document.getElementById('btnDirectSendSingle');
        if (btn) {
            btn.disabled = true;
            btn.innerText = '⏳ Delivering via WhatsApp...';
        }

        try {
            const response = await window.apiFetch('/assistant/recipient/' + recipientId + '/send-direct', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ campaign_id: campaignId })
            });

            if (!response) {
                if (btn) btn.disabled = false;
                return;
            }

            const data = await response.json();
            if (data && data.success) {
                if (btn) btn.innerText = '✅ Sent! Loading next...';
                setTimeout(() => {
                    window.location.reload();
                }, 400);
            } else {
                alert('Direct Dispatch Error: ' + ((data && data.error) || 'Failed to dispatch'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = '⚡ Send Direct & Next Contact (Spacebar)';
                }
            }
        } catch (e) {
            alert('Network error: ' + e.message);
            if (btn) {
                btn.disabled = false;
                btn.innerText = '⚡ Send Direct & Next Contact (Spacebar)';
            }
        }
    }

    // Auto-Pilot Runner Logic
    function toggleAutoPilot() {
        const delay = parseInt(document.getElementById('autoPilotDelaySelect').value) || 6;
        sessionStorage.setItem('wacm_autopilot_active', '1');
        sessionStorage.setItem('wacm_autopilot_delay', delay);
        startAutoPilotExecution(delay);
    }

    function stopAutoPilot() {
        sessionStorage.removeItem('wacm_autopilot_active');
        if (autoPilotInterval) clearInterval(autoPilotInterval);
        
        document.getElementById('autoPilotBadge').innerText = 'Inactive';
        document.getElementById('autoPilotBadge').className = 'badge badge-neutral';
        document.getElementById('btnStartAutoPilot').style.display = 'block';
        document.getElementById('btnStopAutoPilot').style.display = 'none';
        document.getElementById('autoPilotCountdownBox').style.display = 'none';
    }

    function startAutoPilotExecution(delay) {
        // Update UI
        document.getElementById('autoPilotBadge').innerText = 'Auto-Pilot Active';
        document.getElementById('autoPilotBadge').className = 'badge badge-success';
        document.getElementById('btnStartAutoPilot').style.display = 'none';
        document.getElementById('btnStopAutoPilot').style.display = 'block';
        document.getElementById('autoPilotCountdownBox').style.display = 'block';

        // 1. Dispatch directly via socket
        dispatchDirectSingle();
    }

    // Auto-resume Auto-Pilot if was previously running across page reloads
    document.addEventListener('DOMContentLoaded', () => {
        if (sessionStorage.getItem('wacm_autopilot_active') === '1') {
            const delay = parseInt(sessionStorage.getItem('wacm_autopilot_delay')) || 6;
            const select = document.getElementById('autoPilotDelaySelect');
            if (select) select.value = delay;
            
            // Short countdown then dispatch next
            let remaining = delay;
            const display = document.getElementById('autoPilotTimerDisplay');
            document.getElementById('autoPilotBadge').innerText = 'Auto-Pilot Active';
            document.getElementById('autoPilotBadge').className = 'badge badge-success';
            document.getElementById('btnStartAutoPilot').style.display = 'none';
            document.getElementById('btnStopAutoPilot').style.display = 'block';
            document.getElementById('autoPilotCountdownBox').style.display = 'block';
            display.innerText = remaining + 's';

            autoPilotInterval = setInterval(() => {
                remaining--;
                display.innerText = remaining + 's';
                if (remaining <= 0) {
                    clearInterval(autoPilotInterval);
                    dispatchDirectSingle();
                }
            }, 1000);
        }
    });

    // Keyboard Shortcuts: Press Space to trigger Direct Dispatch, Esc to Stop Auto-Pilot
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.code === 'Space') {
            e.preventDefault();
            dispatchDirectSingle();
        } else if (e.code === 'Escape') {
            stopAutoPilot();
        }
    });
    </script>
<?php endif; ?>
