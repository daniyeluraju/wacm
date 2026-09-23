<?php if (!empty($flashes)): ?>
    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
        <?php foreach ($flashes as $flash): ?>
            <?php 
                $typeClass = match($flash['type']) {
                    'success' => 'badge-success',
                    'error' => 'badge-danger',
                    'warning' => 'badge-warning',
                    default => 'badge-info',
                };
            ?>
            <div class="card" style="padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid var(--accent-<?= $flash['type'] === 'error' ? 'rose' : ($flash['type'] === 'warning' ? 'amber' : ($flash['type'] === 'success' ? 'emerald' : 'primary')) ?>);">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge <?= $typeClass ?>"><?= strtoupper(e($flash['type'])) ?></span>
                    <span style="font-size: 0.9rem; color: var(--text-primary);"><?= e($flash['message']) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
