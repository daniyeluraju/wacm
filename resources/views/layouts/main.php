<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle ?? 'WACM - WhatsApp Assistant & Contact Manager') ?></title>
    
    <!-- CSS Design System -->
    <link rel="stylesheet" href="/assets/css/app.css">

    <!-- Chart.js for Visual Reports & Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <?= \App\Core\View::partial('partials/sidebar') ?>

        <!-- Main Content Wrapper -->
        <div class="main-content">
            <!-- Top Navbar -->
            <?= \App\Core\View::partial('partials/navbar', ['pageTitle' => $pageTitle ?? 'WACM Console']) ?>

            <!-- Content Body -->
            <main class="content-body">
                <!-- Flash Notification Messages -->
                <?= \App\Core\View::partial('partials/flash', ['flashes' => $flashes ?? []]) ?>

                <!-- Page Injected Content -->
                <?= $content ?? '' ?>
            </main>

            <!-- Footer -->
            <?= \App\Core\View::partial('partials/footer') ?>
        </div>
    </div>

    <!-- JS Client Libraries -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
