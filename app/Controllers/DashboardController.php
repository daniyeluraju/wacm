<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Contact;
use App\Services\AnalyticsService;

class DashboardController extends BaseController
{
    public function index(Request $request): Response
    {
        $analytics = (new AnalyticsService())->getDashboardMetrics();

        // Recent activity
        $recentActivities = ActivityLog::all('id', 'DESC');
        $recentActivities = array_slice($recentActivities, 0, 8);

        // Recent campaigns
        $recentCampaigns = Campaign::all('id', 'DESC');
        $recentCampaigns = array_slice($recentCampaigns, 0, 5);

        return $this->render('dashboard/index', [
            'pageTitle' => 'Executive Dashboard - WACM',
            'metrics' => $analytics,
            'recentActivities' => $recentActivities,
            'recentCampaigns' => $recentCampaigns,
        ], 'layouts/main');
    }
}
