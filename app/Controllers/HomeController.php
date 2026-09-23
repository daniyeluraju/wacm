<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class HomeController extends BaseController
{
    public function index(Request $request): Response
    {
        // Gather system environment & health diagnostics
        $dbHealth = Database::testConnection();
        $tableCount = 0;
        $tablesList = [];

        if ($dbHealth['status']) {
            try {
                $pdo = Database::connection();
                $stmt = $pdo->query("SHOW TABLES");
                $tablesList = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                $tableCount = count($tablesList);
            } catch (\Throwable $e) {
                $tableCount = 0;
            }
        }

        $userCount = 0;
        if ($dbHealth['status'] && in_array('users', $tablesList)) {
            try {
                $userCount = User::count();
            } catch (\Throwable $e) {
                $userCount = 0;
            }
        }

        $storagePaths = [
            'logs' => config('storage.paths.logs'),
            'uploads' => config('storage.paths.uploads'),
            'temporary' => config('storage.paths.temporary'),
            'exports' => config('storage.paths.exports'),
        ];

        $storageStatus = [];
        foreach ($storagePaths as $key => $path) {
            $storageStatus[$key] = [
                'path' => $path,
                'exists' => file_exists($path),
                'writable' => is_writable($path),
            ];
        }

        $phpExtensions = [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'fileinfo' => extension_loaded('fileinfo'),
            'openssl' => extension_loaded('openssl'),
            'json' => extension_loaded('json'),
            'gd' => extension_loaded('gd'),
        ];

        return $this->render('home/index', [
            'pageTitle' => 'System Health & Environment - Phase 1 Foundation',
            'dbHealth' => $dbHealth,
            'tableCount' => $tableCount,
            'tablesList' => $tablesList,
            'userCount' => $userCount,
            'storageStatus' => $storageStatus,
            'phpExtensions' => $phpExtensions,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function ping(Request $request): Response
    {
        return $this->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'env' => config('app.env'),
            'timestamp' => time(),
            'database' => Database::testConnection(),
        ]);
    }
}
