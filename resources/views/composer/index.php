<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.25rem;">Message Composer & Drafts</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Author personalized templates with placeholders and optional image attachments.</p>
    </div>
    <div>
        <a href="/composer/create" class="btn btn-primary">
            <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create New Draft
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg style="width: 20px; height: 20px; color: var(--accent-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Message Drafts (<?= count($drafts) ?>)
        </h3>
        <span class="badge badge-info">Placeholders Enabled</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Message Snippet</th>
                    <th>Media</th>
                    <th>Created</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($drafts)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No message drafts created yet. <a href="/composer/create">Create your first template</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($drafts as $d): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);"><?= e($d->title) ?></div>
                            </td>
                            <td style="max-width: 350px;">
                                <div style="font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e(mb_substr($d->content, 0, 80)) ?>...
                                </div>
                            </td>
                            <td>
                                <?php if ($d->has_attachment): ?>
                                    <span class="badge badge-success"><span class="badge-dot"></span> Image Attached</span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Text Only</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($d->created_at)) ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.5rem;">
                                    <a href="/composer/<?= e($d->id) ?>/duplicate" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;" title="Duplicate Draft">
                                        Copy
                                    </a>
                                    <a href="/composer/<?= e($d->id) ?>/edit" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">
                                        Edit
                                    </a>
                                    <form method="POST" action="/composer/<?= e($d->id) ?>/delete" onsubmit="return confirm('Archive draft <?= addslashes(e($d->title)) ?>?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">Archive</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($archivedDrafts)): ?>
<!-- Archived Drafts Section -->
<div class="card" style="margin-top: 1.5rem; border-color: rgba(239,68,68,0.2);">
    <div class="card-header" style="cursor:pointer; user-select:none;" onclick="toggleArchived()">
        <h3 class="card-title" style="color: var(--text-muted);">
            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v4m4-4v4" /></svg>
            Archived Drafts (<?= count($archivedDrafts) ?>) — click to expand
        </h3>
        <span class="badge badge-neutral" style="font-size:0.7rem;">Hidden from Campaigns</span>
    </div>
    <div id="archivedSection" style="display:none;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message Snippet</th>
                        <th>Media</th>
                        <th>Created</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivedDrafts as $d): ?>
                        <tr style="opacity: 0.65;">
                            <td>
                                <div style="font-weight:600; color:var(--text-muted); text-decoration:line-through;"><?= e($d->title) ?></div>
                            </td>
                            <td style="max-width:350px;">
                                <div style="font-size:0.85rem; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= e(mb_substr($d->content, 0, 80)) ?>...
                                </div>
                            </td>
                            <td>
                                <?php if ($d->has_attachment): ?>
                                    <span class="badge badge-neutral">Image</span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.8rem;">Text Only</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.8rem; color:var(--text-muted);"><?= date('M j, Y', strtotime($d->created_at)) ?></td>
                            <td style="text-align:right;">
                                <form method="POST" action="/composer/<?= e($d->id) ?>/unarchive" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-secondary" style="padding:0.35rem 0.65rem; font-size:0.75rem; color:#34d399; border-color:rgba(52,211,153,0.4);" title="Restore this draft">
                                        ↩ Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function toggleArchived() {
    const sec = document.getElementById('archivedSection');
    sec.style.display = sec.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php endif; ?>
