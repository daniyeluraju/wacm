<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\CleanupHistory;
use App\Models\StorageSetting;
use App\Services\CleanupService;
use App\Services\StorageUsageService;

class CleanupController extends BaseController
{
    private CleanupService $cleanupService;
    private StorageUsageService $storageUsageService;

    public function __construct()
    {
        $this->cleanupService = new CleanupService();
        $this->storageUsageService = new StorageUsageService();
    }

    public function index(Request $request): Response
    {
        $storageMetrics = $this->storageUsageService->getStorageMetrics();
        $history = CleanupHistory::all('id', 'DESC');
        $history = array_slice($history, 0, 15);

        return $this->render('storage/index', [
            'pageTitle' => 'Storage & Automatic Cleanup - WACM',
            'storage' => $storageMetrics,
            'history' => $history,
        ], 'layouts/main');
    }

    public function run(Request $request): Response
    {
        $user = Session::get('user');
        $type = $request->input('force') ? 'manual_force' : 'manual';

        $result = $this->cleanupService->runCleanup($type, null, $user['id'] ?? null);

        Session::flash('success', "Cleanup executed successfully! {$result['files_deleted']} files removed ({$result['bytes_formatted']} released).");
        return $this->redirect('/storage');
    }
}
