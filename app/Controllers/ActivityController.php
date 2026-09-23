<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\AuditLog;

class ActivityController extends BaseController
{
    public function index(Request $request): Response
    {
        $actionFilter = $request->query('action', '');
        $statusFilter = $request->query('status', '');

        $activities = ActivityLog::all('id', 'DESC');
        if (!empty($actionFilter)) {
            $activities = array_filter($activities, fn($a) => $a->action_type === $actionFilter);
        }
        if (!empty($statusFilter)) {
            $activities = array_filter($activities, fn($a) => $a->status === $statusFilter);
        }

        $auditLogs = AuditLog::all('id', 'DESC');
        $auditLogs = array_slice($auditLogs, 0, 50);

        return $this->render('activity/index', [
            'pageTitle' => 'Activity History & Audit Logs - WACM',
            'activities' => $activities,
            'auditLogs' => $auditLogs,
            'actionFilter' => $actionFilter,
            'statusFilter' => $statusFilter,
        ], 'layouts/main');
    }

    public function export(Request $request): Response
    {
        $activities = ActivityLog::all('id', 'DESC');
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['ID', 'User ID', 'Campaign ID', 'Contact ID', 'Action Type', 'Status', 'Notes', 'IP Address', 'User Agent', 'Timestamp']);

        foreach ($activities as $a) {
            fputcsv($output, [
                $a->id,
                $a->user_id,
                $a->campaign_id,
                $a->contact_id,
                $a->action_type,
                $a->status,
                $a->notes,
                $a->ip_address,
                $a->user_agent,
                $a->created_at,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wacm_activity_logs_' . date('Ymd_His') . '.csv"',
        ]);
    }
}
