<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\MessageDraft;
use PDO;

class CampaignService
{
    /**
     * Compute eligibility statistics for a proposed campaign
     */
    public function calculateEligibility(int|string|null $listId, int $draftId): array
    {
        $pdo = Database::connection();
        
        if (empty($listId) || $listId === 'all' || (int)$listId === 0) {
            $sql = "SELECT c.* FROM contacts c WHERE c.is_deleted = 0";
            $stmt = $pdo->query($sql);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "
                SELECT c.* 
                FROM contacts c
                JOIN contact_list_members clm ON c.id = clm.contact_id
                WHERE clm.list_id = :list_id AND c.is_deleted = 0
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['list_id' => (int)$listId]);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // If selected list has 0 members in membership table, fallback to all active contacts
            if (empty($contacts)) {
                $stmtAll = $pdo->query("SELECT c.* FROM contacts c WHERE c.is_deleted = 0");
                $contacts = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $total = count($contacts);
        $eligible = 0;
        $optedOut = 0;
        $invalid = 0;
        $missingConsent = 0;
        $eligibleContacts = [];

        foreach ($contacts as $c) {
            if ($c['opt_out_status'] == 1) {
                $optedOut++;
                continue;
            }

            if (!ContactService::isValidPhone($c['phone_normalized'])) {
                $invalid++;
                continue;
            }

            if ($c['consent_status'] === 'denied' || $c['consent_status'] === 'revoked') {
                $missingConsent++;
                continue;
            }

            if ($c['consent_status'] === 'unspecified' || $c['consent_status'] === 'pending') {
                $missingConsent++;
            }

            $eligible++;
            $eligibleContacts[] = $c;
        }

        return [
            'total' => $total,
            'eligible' => $eligible,
            'opted_out' => $optedOut,
            'invalid' => $invalid,
            'missing_consent' => $missingConsent,
            'eligible_contacts' => $eligibleContacts,
        ];
    }

    /**
     * Create campaign and populate recipients
     */
    public function createCampaign(array $data, ?int $userId = null): ?Campaign
    {
        $rawListId = $data['contact_list_id'] ?? null;
        $listId = (!empty($rawListId) && $rawListId !== 'all' && is_numeric($rawListId) && (int)$rawListId > 0) ? (int)$rawListId : null;
        $draftId = (int)($data['message_draft_id'] ?? 0);

        $draft = MessageDraft::find($draftId);
        if (!$draft) return null;

        $campaign = Campaign::create([
            'name' => trim($data['name']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'contact_list_id' => $listId,
            'message_draft_id' => $draftId,
            'status' => 'ready_for_review',
            'pacing_min_seconds' => !empty($data['pacing_min_seconds']) ? (int)$data['pacing_min_seconds'] : 32,
            'pacing_max_seconds' => !empty($data['pacing_max_seconds']) ? (int)$data['pacing_max_seconds'] : 40,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$campaign) return null;

        // Populate campaign_recipients
        $pdo = Database::connection();

        if (empty($listId)) {
            $sql = "SELECT c.* FROM contacts c WHERE c.is_deleted = 0";
            $stmt = $pdo->query($sql);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "
                SELECT c.* 
                FROM contacts c
                JOIN contact_list_members clm ON c.id = clm.contact_id
                WHERE clm.list_id = :list_id AND c.is_deleted = 0
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['list_id' => $listId]);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Graceful fallback to all contacts if list has 0 explicit members
            if (empty($contacts)) {
                $stmtAll = $pdo->query("SELECT c.* FROM contacts c WHERE c.is_deleted = 0");
                $contacts = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        foreach ($contacts as $c) {
            $status = 'pending';
            if ($c['opt_out_status'] == 1) {
                $status = 'opted_out';
            } elseif (!ContactService::isValidPhone($c['phone_normalized'])) {
                $status = 'invalid';
            } elseif ($c['consent_status'] === 'denied' || $c['consent_status'] === 'revoked') {
                $status = 'skipped';
            }

            // Prepare personalized message
            $preparedMsg = ComposerService::replaceVariables($draft->content, $c);

            CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $c['id'],
                'status' => $status,
                'prepared_message' => $preparedMsg,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        ActivityLog::log('Campaign Created', 'Success', "Created campaign '{$campaign->name}' with " . count($contacts) . " recipients", (int)$campaign->id, null);
        AuditLog::log('Campaign Created', 'Campaign', $campaign->id);

        return $campaign;
    }

    /**
     * Start / Launch campaign
     */
    public function startCampaign(int $campaignId): bool
    {
        $campaign = Campaign::find($campaignId);
        if (!$campaign) return false;

        $res = Campaign::update($campaignId, [
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::log('Campaign Started', 'Success', "Started manual assistant for '{$campaign->name}'", $campaignId, null);
        return $res;
    }

    /**
     * Pause campaign
     */
    public function pauseCampaign(int $campaignId): bool
    {
        $res = Campaign::update($campaignId, [
            'status' => 'paused',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        ActivityLog::log('Campaign Paused', 'Success', "Campaign paused", $campaignId, null);
        return $res;
    }

    /**
     * Cancel campaign
     */
    public function cancelCampaign(int $campaignId): bool
    {
        $res = Campaign::update($campaignId, [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        ActivityLog::log('Campaign Cancelled', 'Success', "Campaign cancelled", $campaignId, null);
        return $res;
    }

    /**
     * Check if campaign is finished and transition to completed
     */
    public function checkCompletion(int $campaignId): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as pending_count 
            FROM campaign_recipients 
            WHERE campaign_id = :cid AND status IN ('pending', 'eligible', 'chat_opened', 'message_prepared')
        ");
        $stmt->execute(['cid' => $campaignId]);
        $pending = (int)$stmt->fetchColumn();

        if ($pending === 0) {
            Campaign::update($campaignId, [
                'status' => 'cleanup_pending',
                'completed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            ActivityLog::log('Campaign Completed', 'Success', "All recipients processed. Status moved to cleanup_pending", $campaignId, null);
            return true;
        }

        return false;
    }
}
