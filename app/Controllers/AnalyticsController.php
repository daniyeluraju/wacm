<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AnalyticsService;

class AnalyticsController extends BaseController
{
    private AnalyticsService $analyticsService;

    public function __construct()
    {
        $this->analyticsService = new AnalyticsService();
    }

    public function index(Request $request): Response
    {
        $metrics = $this->analyticsService->getDashboardMetrics();

        return $this->render('analytics/index', [
            'pageTitle' => 'Analytics & Operational Reports - WACM',
            'metrics' => $metrics,
        ], 'layouts/main');
    }
}
