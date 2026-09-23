<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\MessageDraft;
use PDO;

class AssistantService
{
    /**
     * Fetch next eligible or in-progress recipient for a campaign
     */
    public function getNextRecipient(int $campaignId): ?array
    {
        $pdo = Database::connection();
        $sql = "
            SELECT cr.*, c.full_name, c.phone_normalized, c.phone_raw, c.country_code, c.group_name, c.email, c.consent_status, c.opt_out_status, c.custom_field_1, c.custom_field_2
            FROM campaign_recipients cr
            JOIN contacts c ON cr.contact_id = c.id
            WHERE cr.campaign_id = :cid 
              AND cr.status IN ('eligible', 'pending', 'chat_opened', 'message_prepared')
            ORDER BY cr.id ASC
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cid' => $campaignId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        // Fetch draft & optional attachment info
        $campaign = Campaign::find($campaignId);
        $draft = MessageDraft::find((int)$campaign->message_draft_id);
        $attachment = null;
        if ($draft && $draft->has_attachment) {
            $attachment = Attachment::findBy('draft_id', $draft->id);
        }

        // Clean phone number for WhatsApp link (digits only, without +)
        $cleanWaPhone = preg_replace('/[^\d]/', '', $row['phone_normalized']);
        $encodedText = urlencode($row['prepared_message']);
        $waUrl = "https://web.whatsapp.com/send?phone={$cleanWaPhone}&text={$encodedText}";

        return [
            'recipient' => $row,
            'campaign' => $campaign,
            'draft' => $draft,
            'attachment' => $attachment,
            'wa_url' => $waUrl,
            'pacing_seconds' => rand($campaign->pacing_min_seconds ?? 32, $campaign->pacing_max_seconds ?? 40),
        ];
    }

    /**
     * Record Chat Opened event
     */
    public function markChatOpened(int $recipientId): bool
    {
        $rec = CampaignRecipient::find($recipientId);
        if (!$rec) return false;

        $res = CampaignRecipient::update($recipientId, [
            'status' => 'chat_opened',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::log('Chat Opened', 'Success', "Opened chat for contact ID {$rec->contact_id}", (int)$rec->campaign_id, (int)$rec->contact_id);
        return $res;
    }

    /**
     * Record User Marked Completed (User explicitly clicked confirm after sending manually)
     */
    public function markCompleted(int $recipientId, string $notes = ''): bool
    {
        $rec = CampaignRecipient::find($recipientId);
        if (!$rec) return false;

        $res = CampaignRecipient::update($recipientId, [
            'status' => 'user_marked_completed',
            'action_notes' => !empty($notes) ? trim($notes) : 'User confirmed completion via assistant',
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::log('User Marked Completed', 'Success', "User recorded manual completion for contact ID {$rec->contact_id}", (int)$rec->campaign_id, (int)$rec->contact_id);

        // Check if campaign is now completed
        (new CampaignService())->checkCompletion((int)$rec->campaign_id);

        return $res;
    }

    /**
     * Skip recipient
     */
    public function skipRecipient(int $recipientId, string $reason = 'User skipped'): bool
    {
        $rec = CampaignRecipient::find($recipientId);
        if (!$rec) return false;

        $res = CampaignRecipient::update($recipientId, [
            'status' => 'skipped',
            'action_notes' => trim($reason),
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::log('Recipient Skipped', 'Success', "Recipient skipped: {$reason}", (int)$rec->campaign_id, (int)$rec->contact_id);
        (new CampaignService())->checkCompletion((int)$rec->campaign_id);

        return $res;
    }
}
