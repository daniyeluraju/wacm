<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle ?? 'Authentication - WACM') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: radial-gradient(circle at top, rgba(37, 211, 102, 0.05), transparent 70%), var(--bg-main);
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background-color: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Brand -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-bottom: 2rem;">
                <div class="brand-icon">W</div>
                <div>
                    <h2 class="brand-name" style="font-size: 1.5rem; line-height: 1.1;">WACM</h2>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">WhatsApp Assistant & Contact Manager</p>
                </div>
            </div>

            <!-- Flashes -->
            <?= \App\Core\View::partial('partials/flash', ['flashes' => $flashes ?? []]) ?>

            <!-- Content -->
            <?= $content ?? '' ?>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>
