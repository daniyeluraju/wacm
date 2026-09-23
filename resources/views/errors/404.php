<div style="text-align: center; padding: 4rem 1rem;">
    <div style="font-family: var(--font-heading); font-size: 5rem; font-weight: 800; color: var(--accent-primary); line-height: 1; margin-bottom: 1rem;">404</div>
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Resource Or Route Not Found</h2>
    <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 2rem;">
        The requested path <code style="color: #38bdf8;"><?= e($path ?? '/') ?></code> does not exist on this server.
    </p>
    <a href="/" class="btn btn-primary">Return to Console</a>
</div>
