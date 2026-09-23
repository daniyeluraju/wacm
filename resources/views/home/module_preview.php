<div class="card" style="text-align: center; padding: 4rem 2rem;">
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: var(--radius-lg); background: rgba(59, 130, 246, 0.15); color: var(--accent-primary); margin-bottom: 1.5rem;">
        <svg style="width: 32px; height: 32px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
    </div>
    
    <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; font-family: var(--font-heading);"><?= e($moduleTitle) ?></h2>
    <p style="color: var(--text-secondary); max-width: 550px; margin: 0 auto 1.5rem; font-size: 0.95rem;">
        This module's underlying database schema, models, relationships, and security middleware have been initialized in <strong>Phase 1</strong>. Complete UI controllers and business services will be enabled in subsequent phases.
    </p>

    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="/" class="btn btn-secondary">&larr; Return to System Health</a>
        <a href="/health" target="_blank" class="btn btn-primary">Verify API Endpoint</a>
    </div>
</div>
