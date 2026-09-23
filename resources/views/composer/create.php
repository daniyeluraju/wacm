<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Create Message Draft</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Author message templates with dynamic contact placeholders and preview the rendered output in real time.</p>
    </div>
    <div>
        <a href="/composer" class="btn btn-secondary">&larr; Back to Drafts</a>
    </div>
</div>

<div class="grid-cols-2">
    <!-- Editor Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Message Editor</h3>
            <span id="charCount" class="badge badge-neutral">0 characters</span>
        </div>

        <form method="POST" action="/composer" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Draft Title *</label>
                <input type="text" name="title" value="<?= e(old('title')) ?>" required placeholder="e.g. VIP Welcome & Onboarding Notice" style="width: 100%; padding: 0.75rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none;">
            </div>

            <!-- Placeholders Picker Pills + Link Inserter -->
            <div style="margin-bottom: 0.75rem;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.35rem;">Click to Insert Variable:</label>
                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;">
                    <?php 
                    $vars = ['{{name}}', '{{phone}}', '{{group_name}}', '{{email}}', '{{custom_field_1}}', '{{custom_field_2}}'];
                    foreach ($vars as $v):
                    ?>
                        <button type="button" onclick="insertVariable('<?= $v ?>')" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; font-family: var(--font-mono); color: #38bdf8; border-color: var(--border-subtle);">
                            + <?= $v ?>
                        </button>
                    <?php endforeach; ?>
                    <!-- Link Insert Button -->
                    <button type="button" onclick="insertLink()" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; color: #a78bfa; border-color: rgba(167,139,250,0.4); background: rgba(167,139,250,0.08); display:flex; align-items:center; gap:0.25rem;">
                        <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        🔗 Insert Link
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Message Content *</label>
                <textarea id="messageContent" name="content" rows="8" required placeholder="Hello {{name}},&#10;&#10;Thank you for being a member of {{group_name}}...&#10;&#10;Best regards," style="width: 100%; padding: 0.85rem 1rem; background-color: var(--bg-input); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.9rem; outline: none; line-height: 1.5; resize: vertical;"><?= e(old('content')) ?></textarea>
            </div>

            <div style="margin-bottom: 1.5rem; padding: 1.25rem; background-color: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.35rem;">Optional Image Attachment</label>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">Allowed formats: JPG, PNG, WebP (Max 10MB)</p>
                <input type="file" id="attachmentInput" name="attachment" accept="image/jpeg,image/png,image/webp" onchange="handleAttachmentChange(event)" style="font-size: 0.85rem; color: var(--text-secondary);">
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Save Draft
                </button>
                <a href="/composer" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Live Preview Card (WhatsApp Bubble Simulation) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg style="width: 20px; height: 20px; color: var(--accent-wa);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                Personalized Preview Simulation
            </h3>
            <button type="button" onclick="copyPreviewText()" class="btn btn-secondary" style="padding: 0.25rem 0.65rem; font-size: 0.75rem;">Copy Text</button>
        </div>

        <div style="background: #0b141a; border-radius: var(--radius-lg); padding: 1.5rem; min-height: 280px; display: flex; flex-direction: column; justify-content: flex-end;">
            <!-- Message Bubble Container -->
            <div id="previewBubbleContainer" style="background-color: #005c4b; color: #e9edef; padding: 0.5rem; border-radius: 8px 8px 0px 8px; max-width: 90%; align-self: flex-end; box-shadow: 0 1px 0.5px rgba(11,20,26,.13); font-size: 0.9rem;">
                <div id="previewImageWrapper" style="display: none; margin-bottom: 0.5rem; border-radius: 6px; overflow: hidden; max-height: 260px; background: rgba(0,0,0,0.2);">
                    <img id="previewImage" src="" alt="Attached image preview" style="width: 100%; height: auto; max-height: 260px; object-fit: contain; display: block; margin: 0 auto; border-radius: 6px;">
                </div>
                <div style="padding: 0.25rem 0.5rem; white-space: pre-wrap; word-break: break-word; line-height: 1.45;" id="previewBubble">Hello Alice Johnson,

Thank you for connecting with us! We have set up your account for VIP Clients.

Best regards,</div>
            </div>
            <div style="align-self: flex-end; font-size: 0.65rem; color: #8696a0; margin-top: 4px;">Just now &bull; Simulated Output</div>
        </div>

        <!-- Compliance Notice on proactive business templates -->
        <div style="margin-top: 1.25rem; font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
            <strong style="color: var(--accent-amber);">Compliance Notice:</strong> Official WhatsApp Business Policy requires proactive business messages outside the 24-hour window to use pre-approved templates when utilizing enterprise integrations.
        </div>
    </div>
</div>

<script>
function insertVariable(v) {
    const txt = document.getElementById('messageContent');
    const start = txt.selectionStart;
    const end = txt.selectionEnd;
    txt.value = txt.value.substring(0, start) + v + txt.value.substring(end);
    txt.focus();
    txt.selectionStart = txt.selectionEnd = start + v.length;
    updatePreview();
}

function handleAttachmentChange(e) {
    const file = e.target.files[0];
    const wrapper = document.getElementById('previewImageWrapper');
    const img = document.getElementById('previewImage');
    if (file) {
        img.src = URL.createObjectURL(file);
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
        img.src = '';
    }
}

function updatePreview() {
    const raw = document.getElementById('messageContent').value || 'Enter message content to preview...';
    document.getElementById('charCount').innerText = raw.length + ' characters';

    const rendered = raw
        .replace(/{{name}}/g, 'Alice Johnson')
        .replace(/{{phone}}/g, '+1 (555) 234-5678')
        .replace(/{{group_name}}/g, 'VIP Clients')
        .replace(/{{email}}/g, 'alice@example.com')
        .replace(/{{custom_field_1}}/g, 'Account #A102')
        .replace(/{{custom_field_2}}/g, 'Tier 1');

    document.getElementById('previewBubble').innerHTML = renderWhatsAppMarkdown(rendered);
}

/**
 * Render WhatsApp message markdown into HTML:
 *   *bold*  _italic_  ~strikethrough~  `mono`  URLs  newlines
 */
function renderWhatsAppMarkdown(str) {
    // 1. Escape HTML special chars first (to prevent XSS)
    let s = str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    // 2. Protect URLs from markdown processing (replace temporarily)
    const urls = [];
    s = s.replace(/(https?:\/\/[^\s<>"'\u2019\u2018]+)/g, (match) => {
        urls.push(match);
        return `\x00URL${urls.length - 1}\x00`;
    });

    // 3. WhatsApp markdown rules (must not span newlines)
    // Bold: *text*
    s = s.replace(/\*([^\*\n]+)\*/g, '<strong>$1</strong>');
    // Italic: _text_
    s = s.replace(/(?<![\w])_([^_\n]+)_(?![\w])/g, '<em>$1</em>');
    // Strikethrough: ~text~
    s = s.replace(/~([^~\n]+)~/g, '<del>$1</del>');
    // Monospace: `text`
    s = s.replace(/`([^`\n]+)`/g, '<code style="background:rgba(0,0,0,0.3);padding:1px 4px;border-radius:3px;font-family:monospace;font-size:0.88em;">$1</code>');

    // 4. Restore URLs as clickable links
    s = s.replace(/\x00URL(\d+)\x00/g, (_, i) => {
        const u = urls[parseInt(i)];
        return `<a href="${u}" target="_blank" rel="noopener" style="color:#53bdeb;text-decoration:underline;word-break:break-all;">${u}</a>`;
    });

    // 5. Convert newlines to <br>
    s = s.replace(/\n/g, '<br>');

    return s;
}

function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function insertLink() {
    const url = prompt('Enter the URL to insert (e.g. https://example.com):');
    if (!url || !url.trim()) return;
    const clean = url.trim().startsWith('http') ? url.trim() : 'https://' + url.trim();
    const txt = document.getElementById('messageContent');
    const start = txt.selectionStart;
    const end   = txt.selectionEnd;
    txt.value = txt.value.substring(0, start) + clean + txt.value.substring(end);
    txt.focus();
    txt.selectionStart = txt.selectionEnd = start + clean.length;
    updatePreview();
}

function copyPreviewText() {
    // Copy plain text (no HTML) even though preview shows clickable links
    const text = document.getElementById('messageContent').value
        .replace(/{{name}}/g, 'Alice Johnson')
        .replace(/{{phone}}/g, '+1 (555) 234-5678')
        .replace(/{{group_name}}/g, 'VIP Clients')
        .replace(/{{email}}/g, 'alice@example.com')
        .replace(/{{custom_field_1}}/g, 'Account #A102')
        .replace(/{{custom_field_2}}/g, 'Tier 1');
    window.copyToClipboard(text);
}

document.getElementById('messageContent').addEventListener('input', updatePreview);
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
