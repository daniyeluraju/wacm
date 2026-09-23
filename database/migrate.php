<?php

/**
 * WACM Database Migration & Seeder CLI Runner
 * Usage: php database/migrate.php [--fresh] [--seed]
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Autoloader::addNamespace('App', dirname(__DIR__) . '/app');

require_once dirname(__DIR__) . '/app/Helpers/functions.php';
require_once dirname(__DIR__) . '/app/Core/Env.php';
\App\Core\Env::load(dirname(__DIR__) . '/.env');

$dbUrl = env('DATABASE_URL', env('MYSQL_URL'));
if ($dbUrl) {
    $parsed = parse_url($dbUrl);
    if ($parsed) {
        $dbHost = $parsed['host'] ?? '127.0.0.1';
        $dbPort = $parsed['port'] ?? 3306;
        $dbName = !empty($parsed['path']) ? ltrim($parsed['path'], '/') : 'wacm';
        $dbUser = $parsed['user'] ?? 'root';
        $dbPass = $parsed['pass'] ?? '';
    }
} else {
    $dbHost = env('DB_HOST', '127.0.0.1');
    $dbPort = env('DB_PORT', '3306');
    $dbName = env('DB_DATABASE', 'wacm');
    $dbUser = env('DB_USERNAME', 'root');
    $dbPass = env('DB_PASSWORD', '');
}
$dbCharset = env('DB_CHARSET', 'utf8mb4');

echo "=======================================================\n";
echo "  WACM - Database Migration & Setup Tool\n";
echo "=======================================================\n\n";

try {
    // 1. Try connecting directly to the target database first
    $pdo = null;
    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        echo "[1/4] Connected directly to database `{$dbName}`.\n\n";
    } catch (PDOException $ex) {
        // If connecting to specific db failed, try connecting without db name and create it
        echo "[1/4] Attempting to create database `{$dbName}`...\n";
        $rootPdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort};charset={$dbCharset}",
            $dbUser,
            $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "      Database `{$dbName}` is ready.\n\n";

        $pdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    // 3. Run Migrations
    echo "[2/4] Running schema migrations from database/migrations/...\n";
    $migrationFiles = glob(__DIR__ . '/migrations/*.sql');
    sort($migrationFiles);

    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        echo "      Applying: {$filename}... ";
        $sql = file_get_contents($file);
        $pdo->exec($sql);
        echo "[DONE]\n";
    }
    echo "\n";

    // 4. Run Seeders
    echo "[3/4] Running seeders from database/seeders/...\n";
    $seederFiles = glob(__DIR__ . '/seeders/*.sql');
    sort($seederFiles);

    foreach ($seederFiles as $file) {
        $filename = basename($file);
        echo "      Applying: {$filename}... ";
        $sql = file_get_contents($file);
        $pdo->exec($sql);
        echo "[DONE]\n";
    }
    echo "\n";

    // 5. Verification & Summary
    echo "[4/4] Verifying database tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "      Total tables found: " . count($tables) . "\n";
    foreach ($tables as $index => $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
        $rowCount = $countStmt->fetchColumn();
        printf("      [%02d] %-25s (%d records)\n", $index + 1, $table, $rowCount);
    }

    echo "\n=======================================================\n";
    echo "  Migration & Seeding completed successfully!\n";
    echo "  Super Admin: admin@wacm.local  (Password: Admin@123456)\n";
    echo "  Admin User:  manager@wacm.local(Password: Admin@123456)\n";
    echo "  Viewer User: viewer@wacm.local (Password: Viewer@123456)\n";
    echo "=======================================================\n";

} catch (PDOException $e) {
    echo "\n[ERROR] Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
