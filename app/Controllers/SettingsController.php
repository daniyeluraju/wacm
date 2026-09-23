<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\StorageSetting;

class SettingsController extends BaseController
{
    public function index(Request $request): Response
    {
        $settings = [
            'cleanup_enabled' => StorageSetting::get('cleanup_enabled', '1'),
            'cleanup_retention_hours' => StorageSetting::get('cleanup_retention_hours', '24'),
            'cleanup_auto_delete_temp' => StorageSetting::get('cleanup_auto_delete_temp', '1'),
            'storage_warning_threshold_mb' => StorageSetting::get('storage_warning_threshold_mb', '500'),
            'pacing_reminder_min_seconds' => StorageSetting::get('pacing_reminder_min_seconds', '32'),
            'pacing_reminder_max_seconds' => StorageSetting::get('pacing_reminder_max_seconds', '40'),
            'whatsapp_api_mode' => StorageSetting::get('whatsapp_api_mode', 'direct_gateway'),
            'whatsapp_daily_limit' => StorageSetting::get('whatsapp_daily_limit', '1000'),
            'meta_phone_number_id' => StorageSetting::get('meta_phone_number_id', ''),
            'meta_access_token' => StorageSetting::get('meta_access_token', ''),
            'meta_business_account_id' => StorageSetting::get('meta_business_account_id', ''),
        ];

        return $this->render('settings/index', [
            'pageTitle' => 'System Settings & Safety Controls - WACM',
            'settings' => $settings,
        ], 'layouts/main');
    }

    public function update(Request $request): Response
    {
        $keys = [
            'cleanup_enabled',
            'cleanup_retention_hours',
            'cleanup_auto_delete_temp',
            'storage_warning_threshold_mb',
            'pacing_reminder_min_seconds',
            'pacing_reminder_max_seconds',
            'whatsapp_api_mode',
            'whatsapp_daily_limit',
            'meta_phone_number_id',
            'meta_access_token',
            'meta_business_account_id',
        ];

        foreach ($keys as $key) {
            $val = $request->input($key);
            if ($val !== null) {
                StorageSetting::set($key, trim($val));
            }
        }

        AuditLog::log('Settings Updated', 'SystemSettings');
        Session::flash('success', 'System safety & storage settings updated successfully.');
        return $this->redirect('/settings');
    }
}
