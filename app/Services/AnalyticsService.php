<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use PDO;

class AnalyticsService
{
    /**
     * Gather dashboard summary metrics
     */
    public function getDashboardMetrics(): array
    {
        $pdo = Database::connection();

        // Contact Stats
        $totalContacts = Contact::count('is_deleted = 0');
        $optedOutContacts = Contact::count('is_deleted = 0 AND opt_out_status = 1');
        $grantedConsent = Contact::count("is_deleted = 0 AND consent_status = 'granted'");
        $pendingConsent = Contact::count("is_deleted = 0 AND consent_status IN ('pending', 'unspecified')");
        $validContacts = $totalContacts - $optedOutContacts;

        // Campaign Stats
        $totalCampaigns = Campaign::count();
        $inProgressCampaigns = Campaign::count("status = 'in_progress'");
        $completedCampaigns = Campaign::count("status IN ('completed', 'cleanup_pending', 'cleaned_up')");
        $draftCampaigns = Campaign::count("status IN ('draft', 'ready_for_review')");

        // Recipient Outcomes
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count 
            FROM campaign_recipients 
            GROUP BY status
        ");
        $outcomes = [
            'user_marked_completed' => 0,
            'chat_opened' => 0,
            'skipped' => 0,
            'eligible' => 0,
            'opted_out' => 0,
            'invalid' => 0,
        ];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $outcomes[$row['status']] = (int)$row['count'];
        }

        // Storage
        $storageService = new StorageUsageService();
        $storage = $storageService->getStorageMetrics();

        return [
            'contacts' => [
                'total' => $totalContacts,
                'valid' => $validContacts,
                'opted_out' => $optedOutContacts,
                'granted_consent' => $grantedConsent,
                'pending_consent' => $pendingConsent,
            ],
            'campaigns' => [
                'total' => $totalCampaigns,
                'in_progress' => $inProgressCampaigns,
                'completed' => $completedCampaigns,
                'draft' => $draftCampaigns,
            ],
            'recipients' => $outcomes,
            'storage' => $storage,
        ];
    }
}
