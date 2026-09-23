<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading);">Link WhatsApp Device (QR Gateway)</h2>
            <span id="gatewayStatusBadge" class="badge badge-neutral">Checking...</span>
        </div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Scan the QR code once with your phone to link your WhatsApp number for 100% automated direct sending.
        </p>
    </div>
</div>

<div class="grid-cols-2">
    <!-- QR Code Scanning Card -->
    <div class="card" style="text-align: center; padding: 2.5rem 1.5rem;">

        <!-- Loading spinner (only first few seconds) -->
        <div id="qrLoadingBox" style="display: block; padding: 2rem;">
            <div style="width: 48px; height: 48px; border: 4px solid var(--border-medium); border-top-color: var(--accent-wa); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;">Checking gateway daemon...</div>
        </div>

        <!-- Offline / Daemon Not Running state -->
        <div id="offlineBox" style="display: none; padding: 1.5rem 0;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(239,68,68,0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <svg style="width: 44px; height: 44px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M6.343 6.343a9 9 0 000 12.728M8.464 8.464a5 5 0 000 7.072M12 12h.01" />
                </svg>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #ef4444; margin-bottom: 0.5rem; font-family: var(--font-heading);">
                Gateway Daemon Offline
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); max-width: 340px; margin: 0 auto 1.5rem; line-height: 1.6;">
                The local WhatsApp gateway daemon is not running on <code style="background:var(--bg-surface-elevated);padding:2px 6px;border-radius:4px;font-size:0.8rem;">port 3001</code>.
                Start it to enable QR-based device linking.
            </p>
            <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1rem; text-align: left; max-width: 360px; margin: 0 auto 1.25rem;">
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Start Gateway Command</div>
                <code style="font-family: var(--font-mono); font-size: 0.8rem; color: #38bdf8; display: block;">node gateway/server.js</code>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <button onclick="retryConnection()" class="btn btn-secondary" style="font-size: 0.85rem;">
                    <svg style="width:15px;height:15px;display:inline-block;vertical-align:middle;margin-right:4px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Retry Connection
                </button>
                <a href="/assistant" class="btn btn-whatsapp" style="font-size: 0.85rem;">
                    Use Manual Assistant Instead
                </a>
            </div>
        </div>

        <!-- QR Code ready -->
        <div id="qrImageBox" style="display: none;">
            <div style="background: #ffffff; padding: 1rem; border-radius: var(--radius-lg); display: inline-block; margin-bottom: 1.25rem; box-shadow: 0 8px 30px rgba(0,0,0,0.5);">
                <img id="qrImg" src="" alt="WhatsApp QR Code" style="width: 260px; height: 260px; display: block;">
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #f8fafc; margin-bottom: 0.5rem;">
                Scan with your phone's WhatsApp
            </div>
            <p style="font-size: 0.8rem; color: var(--text-muted); max-width: 320px; margin: 0 auto;">
                QR code updates automatically. Point your camera at this code in WhatsApp &gt; Linked Devices.
            </p>
        </div>

        <!-- Connected state -->
        <div id="connectedBox" style="display: none; padding: 1.5rem 0;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(37, 211, 102, 0.15); color: var(--accent-wa); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg style="width: 48px; height: 48px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--accent-wa); margin-bottom: 0.5rem; font-family: var(--font-heading);">
                WhatsApp Device Linked &amp; Ready!
            </h3>
            <div id="connectedUserDetails" style="font-size: 1rem; color: var(--text-primary); font-family: var(--font-mono); margin-bottom: 1.5rem; font-weight: 600;">
                Connected
            </div>
            <p style="font-size: 0.85rem; color: var(--text-secondary); max-width: 380px; margin: 0 auto 2rem;">
                Your local gateway is connected to WhatsApp. When you click <strong>Start Direct Automated Send</strong> on any campaign, messages will be delivered directly and automatically through this number.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="/campaigns" class="btn btn-whatsapp">Go to Campaigns &rarr;</a>
                <button type="button" onclick="disconnectDevice()" class="btn btn-secondary" style="color: var(--accent-rose);">
                    Disconnect Device
                </button>
            </div>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                How to Link Your WhatsApp Device
            </h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; color: var(--accent-wa);">1</div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Start the Gateway Daemon</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Run <code style="background:var(--bg-surface-elevated);padding:1px 5px;border-radius:3px;">node gateway/server.js</code> in a terminal to start the local WhatsApp bridge on port 3001.</div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; color: var(--accent-wa);">2</div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Open WhatsApp on your Phone</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Open your regular WhatsApp or WhatsApp Business mobile application.</div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; color: var(--accent-wa);">3</div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Tap Menu or Settings &gt; Linked Devices</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">On Android: tap the 3 dots menu &gt; <strong>Linked Devices</strong>. On iPhone: tap <strong>Settings &gt; Linked Devices</strong>.</div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; color: var(--accent-wa);">4</div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Tap &quot;Link a Device&quot; &amp; Scan</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Unlock with fingerprint/PIN, point your phone camera at the QR code on the left, and wait 3 seconds!</div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; color: var(--accent-emerald);">&#10003;</div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Automated Direct Sending Active!</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Once scanned, campaigns will send directly and automatically through your phone number in the background.</div>
                </div>
            </div>
        </div>

        <!-- Alternative -->
        <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(167,139,250,0.07); border: 1px solid rgba(167,139,250,0.2); border-radius: var(--radius-md);">
            <div style="font-size: 0.8rem; font-weight: 700; color: #a78bfa; margin-bottom: 0.4rem;">&#9889; No Gateway? Use Manual Mode</div>
            <div style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.5;">
                You can still run campaigns manually using the <a href="/assistant" style="color:#a78bfa;">Manual WA Assistant</a> — it opens WhatsApp Web links for each contact one by one with your pre-written message.
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
let pollInterval = null;
let offlineRetries = 0;
const MAX_RETRIES = 2; // Show offline UI after 2 failed checks

function showState(state) {
    const boxes = { loading: 'qrLoadingBox', offline: 'offlineBox', qr: 'qrImageBox', connected: 'connectedBox' };
    Object.entries(boxes).forEach(([key, id]) => {
        const el = document.getElementById(id);
        if (el) el.style.display = (key === state) ? 'block' : 'none';
    });
}

async function checkStatus() {
    try {
        const response = await window.apiFetch('/gateway/status');
        if (!response) return;
        const data = await response.json();

        const badge = document.getElementById('gatewayStatusBadge');

        if (data.status === 'connected') {
            badge.innerText = 'Connected';
            badge.className = 'badge badge-success';
            showState('connected');
            const userDetails = document.getElementById('connectedUserDetails');
            const userId = data.user?.id ? data.user.id.split('@')[0].split(':')[0] : 'Linked Number';
            userDetails.innerText = '+' + userId;
            clearInterval(pollInterval); // No need to keep polling once connected

        } else if (data.status === 'qr_ready' && data.qr) {
            badge.innerText = 'Scan QR to Link';
            badge.className = 'badge badge-warning';
            showState('qr');
            document.getElementById('qrImg').src = data.qr;
            offlineRetries = 0;

        } else if (data.status === 'offline' || data.error) {
            offlineRetries++;
            if (offlineRetries >= MAX_RETRIES) {
                badge.innerText = 'Offline';
                badge.className = 'badge badge-danger';
                showState('offline');
                clearInterval(pollInterval); // Stop polling — daemon is not running
            }
            // else still showing spinner briefly

        } else {
            // Unknown status — show spinner, keep trying
            badge.innerText = 'Connecting...';
            badge.className = 'badge badge-neutral';
            showState('loading');
        }
    } catch (e) {
        offlineRetries++;
        if (offlineRetries >= MAX_RETRIES) {
            const badge = document.getElementById('gatewayStatusBadge');
            badge.innerText = 'Offline';
            badge.className = 'badge badge-danger';
            showState('offline');
            clearInterval(pollInterval);
        }
        console.error('Gateway poll error:', e);
    }
}

function retryConnection() {
    offlineRetries = 0;
    showState('loading');
    const badge = document.getElementById('gatewayStatusBadge');
    badge.innerText = 'Checking...';
    badge.className = 'badge badge-neutral';
    checkStatus();
    pollInterval = setInterval(checkStatus, 3000);
}

async function disconnectDevice() {
    if (!confirm('Are you sure you want to disconnect this WhatsApp device?')) return;
    try {
        await window.apiFetch('/gateway/disconnect', { method: 'POST' });
        offlineRetries = 0;
        retryConnection();
    } catch (e) {
        alert('Failed to disconnect: ' + e.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    checkStatus();
    pollInterval = setInterval(checkStatus, 3000);
});
</script>
