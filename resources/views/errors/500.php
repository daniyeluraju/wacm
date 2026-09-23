<div style="text-align: center; padding: 4rem 1rem;">
    <div style="font-family: var(--font-heading); font-size: 5rem; font-weight: 800; color: var(--accent-rose); line-height: 1; margin-bottom: 1rem;">500</div>
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Internal Server Error</h2>
    <p style="color: var(--text-secondary); max-width: 550px; margin: 0 auto 2rem;">
        An unexpected error occurred while processing your request. Please check the system log.
    </p>

    <?php if (!empty($debug) && !empty($exception)): ?>
        <div style="max-width: 800px; margin: 0 auto 2rem; text-align: left;" class="card">
            <h4 style="color: var(--accent-rose); margin-bottom: 0.5rem;"><?= e($exception->getMessage()) ?></h4>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">
                File: <?= e($exception->getFile()) ?> (Line <?= e($exception->getLine()) ?>)
            </div>
            <pre class="code-block" style="font-size: 0.75rem; max-height: 300px;"><?= e($exception->getTraceAsString()) ?></pre>
        </div>
    <?php endif; ?>

    <a href="/" class="btn btn-primary">Return to Console</a>
</div>
