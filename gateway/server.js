const express = require('express');
const cors = require('cors');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');
const pino = require('pino');

// ── Global safety nets ─────────────────────────────────────────────────────
// Baileys can throw unhandled rejections (e.g. "Connection Closed" 428)
// when the WA server drops the socket mid-operation. Catching these prevents
// the entire Node process from crashing.
process.on('uncaughtException', (err) => {
    console.error('[uncaughtException] Recovered:', err.message);
    // If the socket is the source, schedule a reconnect
    if (connectionStatus !== 'connected') {
        setTimeout(startSock, 4000);
    }
});

process.on('unhandledRejection', (reason) => {
    const msg = reason?.message || String(reason);
    console.error('[unhandledRejection] Recovered:', msg);
    if (connectionStatus !== 'connected') {
        setTimeout(startSock, 4000);
    }
});
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion
} = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.PORT || 3001;

app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ limit: '50mb', extended: true }));

const AUTH_DIR = path.join(__dirname, 'auth_info_baileys');
if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

let sock = null;
let qrCodeRaw = null;
let qrCodeDataUrl = null;
let connectionStatus = 'disconnected'; // 'disconnected' | 'connecting' | 'qr_ready' | 'connected'
let connectedUser = null;
let lastError = null;

async function startSock() {
    connectionStatus = 'connecting';
    qrCodeRaw = null;
    qrCodeDataUrl = null;
    lastError = null;

    try {
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            logger: pino({ level: 'silent' }),
            printQRInTerminal: false,
            auth: state,
            browser: ['WACM Assistant', 'Chrome', '124.0.0.0'],
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                qrCodeRaw = qr;
                connectionStatus = 'qr_ready';
                try {
                    qrCodeDataUrl = await QRCode.toDataURL(qr, { margin: 2, scale: 8 });
                } catch (e) {
                    console.error('Failed to generate QR data URL:', e);
                }
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const isLoggedOut = statusCode === DisconnectReason.loggedOut;
                const isRestartRequired = statusCode === DisconnectReason.restartRequired;

                connectionStatus = 'disconnected';
                connectedUser = null;
                qrCodeRaw = null;
                qrCodeDataUrl = null;
                lastError = lastDisconnect?.error?.message || 'Connection closed';

                if (isLoggedOut) {
                    console.log('[Gateway] Logged out. Clearing session — please scan QR again.');
                    try { fs.rmSync(AUTH_DIR, { recursive: true, force: true }); } catch (_) {}
                    try { fs.mkdirSync(AUTH_DIR, { recursive: true }); } catch (_) {}
                    setTimeout(startSock, 2000);
                } else {
                    const delay = isRestartRequired ? 1000 : 4000;
                    console.log(`[Gateway] Connection closed (code ${statusCode}). Reconnecting in ${delay}ms...`);
                    setTimeout(startSock, delay);
                }
            } else if (connection === 'open') {
                connectionStatus = 'connected';
                qrCodeRaw = null;
                qrCodeDataUrl = null;
                lastError = null;
                connectedUser = sock.user || { id: 'Linked Device' };
                console.log('WhatsApp Gateway Connected Successfully as:', connectedUser);
            }
        });
    } catch (err) {
        console.error('Error in startSock:', err);
        connectionStatus = 'disconnected';
        lastError = err.message;
    }
}

// REST Endpoints
app.get('/status', (req, res) => {
    res.json({
        status: connectionStatus,
        qr: qrCodeDataUrl,
        user: connectedUser,
        error: lastError,
        timestamp: new Date().toISOString()
    });
});

app.post('/send', async (req, res) => {
    const { phone, message, image_path, image_base64, mimetype, file_name } = req.body;

    if (!phone || (!message && !image_path && !image_base64)) {
        return res.status(400).json({ success: false, error: 'Phone number and message or image are required.' });
    }

    if (connectionStatus !== 'connected' || !sock) {
        return res.status(503).json({
            success: false,
            error: 'WhatsApp Gateway is not connected. Please scan the QR code first.'
        });
    }

    try {
        // Normalize phone number to JID
        const cleanDigits = phone.toString().replace(/\D/g, '');
        const jid = `${cleanDigits}@s.whatsapp.net`;

        // Check if number exists on WhatsApp
        const [result] = await sock.onWhatsApp(jid);
        if (!result || !result.exists) {
            return res.status(404).json({
                success: false,
                error: `Phone number +${cleanDigits} is not registered on WhatsApp.`
            });
        }

        const targetJid = result.jid;
        let msg;
        let hasImage = false;

        // Resolve Image Buffer (from Base64 or File Path)
        let imgBuffer = null;
        let finalMime = mimetype || 'image/png';

        if (image_base64) {
            imgBuffer = Buffer.from(image_base64, 'base64');
            hasImage = true;
        } else if (image_path && fs.existsSync(image_path)) {
            imgBuffer = fs.readFileSync(image_path);
            hasImage = true;
            if (image_path.endsWith('.jpg') || image_path.endsWith('.jpeg')) finalMime = 'image/jpeg';
            else if (image_path.endsWith('.webp')) finalMime = 'image/webp';
        }

        if (hasImage && imgBuffer) {
            console.log(`[${new Date().toISOString()}] Dispatching MEDIA to +${cleanDigits} (${imgBuffer.length} bytes, ${finalMime})`);
            msg = await sock.sendMessage(targetJid, {
                image: imgBuffer,
                mimetype: finalMime,
                caption: message ? message.toString() : '',
                fileName: file_name || 'image.png'
            });
        } else {
            console.log(`[${new Date().toISOString()}] Dispatching TEXT to +${cleanDigits}`);
            msg = await sock.sendMessage(targetJid, { text: (message || '').toString() });
        }

        res.json({
            success: true,
            messageId: msg.key.id,
            to: cleanDigits,
            hasImage: hasImage,
            timestamp: new Date().toISOString()
        });
    } catch (err) {
        console.error('Error sending message via Baileys:', err);
        res.status(500).json({
            success: false,
            error: err.message || 'Failed to dispatch message via WhatsApp socket'
        });
    }
});

app.post('/disconnect', async (req, res) => {
    try {
        if (sock) {
            await sock.logout();
        }
    } catch (e) {}

    try {
        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
        fs.mkdirSync(AUTH_DIR, { recursive: true });
    } catch (e) {}

    connectionStatus = 'disconnected';
    connectedUser = null;
    qrCodeRaw = null;
    qrCodeDataUrl = null;

    setTimeout(startSock, 1500);

    res.json({ success: true, message: 'Disconnected and reset session.' });
});

app.listen(PORT, () => {
    console.log(`WACM WhatsApp Gateway server running on http://127.0.0.1:${PORT}`);
    startSock();
});
