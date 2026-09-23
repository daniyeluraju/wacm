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

class WhatsAppCloudApiService
{
    /**
     * Fetch daily sending usage and limit statistics
     */
    public function getDailyUsage(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM campaign_recipients 
            WHERE status IN ('user_marked_completed', 'direct_sent') 
            AND completed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $usedToday = (int)($row['count'] ?? 0);

        $dailyLimit = (int) \App\Models\StorageSetting::get('whatsapp_daily_limit', 1000);

        return [
            'used_today' => $usedToday,
            'daily_limit' => $dailyLimit,
            'remaining' => max(0, $dailyLimit - $usedToday),
            'limit_reached' => $usedToday >= $dailyLimit,
        ];
    }

    /**
     * Dispatch a single recipient via Official Meta Cloud API or Safe Direct Mode
     */
    public function sendRecipientDirect(int $recipientId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("
            SELECT cr.*, c.full_name, c.phone_normalized, c.opt_out_status, c.is_deleted, 
                   camp.name as campaign_name, camp.message_draft_id
            FROM campaign_recipients cr
            JOIN contacts c ON cr.contact_id = c.id
            JOIN campaigns camp ON cr.campaign_id = camp.id
            WHERE cr.id = :id
        ");
        $stmt->execute(['id' => $recipientId]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rec) {
            return ['success' => false, 'error' => 'Recipient not found.'];
        }

        if ($rec['opt_out_status'] == 1 || $rec['is_deleted'] == 1) {
            return ['success' => false, 'error' => 'Recipient is suppressed or opted-out.'];
        }

        // Check daily limit
        $usage = $this->getDailyUsage();
        if ($usage['limit_reached']) {
            return [
                'success' => false,
                'error' => "Daily messaging limit reached ({$usage['used_today']}/{$usage['daily_limit']}). Please wait or increase daily limit in Settings.",
            ];
        }

        // Fetch API credentials from StorageSetting
        $apiMode = \App\Models\StorageSetting::get('whatsapp_api_mode', 'direct_gateway');
        $phoneNumberId = trim((string)\App\Models\StorageSetting::get('meta_phone_number_id', ''));
        $accessToken = trim((string)\App\Models\StorageSetting::get('meta_access_token', ''));

        $phoneDigits = preg_replace('/\D/', '', $rec['phone_normalized']);
        $messageBody = $rec['prepared_message'];

        $apiResponse = null;
        $statusOutcome = 'user_marked_completed';
        $gatewayNote = 'Direct automated delivery';

        if ($apiMode === 'meta_cloud_api') {
            if (empty($phoneNumberId) || empty($accessToken)) {
                return [
                    'success' => false,
                    'error' => 'Meta WhatsApp Cloud API credentials not configured. Please enter your Phone Number ID & Access Token in Settings.',
                ];
            }

            // Call official Meta Graph API v20.0
            $url = "https://graph.facebook.com/v20.0/{$phoneNumberId}/messages";
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $phoneDigits,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $messageBody,
                ],
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $decoded = json_decode($responseBody, true);
            if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['messages'][0]['id'])) {
                $gatewayNote = "Meta Cloud API (MsgID: {$decoded['messages'][0]['id']})";
                $apiResponse = $decoded;
            } else {
                $errorMsg = $decoded['error']['message'] ?? 'Meta API error code ' . $httpCode;
                return [
                    'success' => false,
                    'error' => "Meta Cloud API error: {$errorMsg}",
                ];
            }
        } else {
            // Check for attachment in message draft
            $imagePath = null;
            $imageBase64 = null;
            $imageMime = null;
            $imageName = null;

            if (!empty($rec['message_draft_id'])) {
                $att = \App\Models\Attachment::getLatestForDraft((int)$rec['message_draft_id']);
                if ($att && !empty($att->file_path)) {
                    if (file_exists($att->file_path)) {
                        $imagePath = $att->file_path;
                    } else {
                        $candidate = dirname(__DIR__, 2) . '/storage/uploads/' . $att->stored_name;
                        if (file_exists($candidate)) {
                            $imagePath = $candidate;
                        }
                    }

                    if ($imagePath && file_exists($imagePath)) {
                        $rawBytes = @file_get_contents($imagePath);
                        if ($rawBytes !== false && strlen($rawBytes) > 0) {
                            $imageBase64 = base64_encode($rawBytes);
                            $imageMime = $att->mime_type ?? 'image/png';
                            $imageName = $att->original_name ?? basename($imagePath);
                        }
                    }
                }
            }

            // Local WhatsApp QR Gateway (Port 3001)
            $url = "http://127.0.0.1:3001/send";
            $payload = [
                'phone' => $phoneDigits,
                'message' => $messageBody,
                'image_path' => $imagePath,
                'image_base64' => $imageBase64,
                'mimetype' => $imageMime,
                'file_name' => $imageName,
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 0 || empty($responseBody)) {
                return [
                    'success' => false,
                    'error' => 'Local WhatsApp Gateway service is not running. Please start the gateway or check Settings.',
                ];
            }

            $decoded = json_decode($responseBody, true);
            if ($httpCode === 200 && isset($decoded['success']) && $decoded['success'] === true) {
                $hasMedia = !empty($decoded['hasImage']);
                $gatewayNote = "Sent via Linked WhatsApp" . ($hasMedia ? " with Image" : "") . " (MsgID: " . ($decoded['messageId'] ?? 'N/A') . ")";
                $apiResponse = $decoded;
            } else {
                $errorMsg = $decoded['error'] ?? 'WhatsApp dispatch failed';
                return [
                    'success' => false,
                    'error' => $errorMsg,
                ];
            }
        }

        // Update recipient record
        $updateStmt = $pdo->prepare("
            UPDATE campaign_recipients 
            SET status = :status, 
                action_notes = :notes, 
                completed_at = NOW(), 
                updated_at = NOW() 
            WHERE id = :id
        ");
        $updateStmt->execute([
            'status' => $statusOutcome,
            'notes' => $gatewayNote,
            'id' => $recipientId,
        ]);

        ActivityLog::log(
            'Direct Message Sent',
            'Success',
            "Automated dispatch to {$rec['full_name']} ({$rec['phone_normalized']})",
            (int)$rec['campaign_id'],
            (int)$rec['contact_id']
        );

        AuditLog::log('Direct Dispatch', 'CampaignRecipient', (int)$recipientId);

        return [
            'success' => true,
            'recipient_id' => $recipientId,
            'name' => $rec['full_name'],
            'phone' => $rec['phone_normalized'],
            'status' => $statusOutcome,
            'note' => $gatewayNote,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Process an automated batch run for an entire campaign
     */
    public function runAutomatedCampaignWave(int $campaignId, int $maxCount = 100): array
    {
        $pdo = Database::connection();

        // Get pending recipients for this campaign
        $stmt = $pdo->prepare("
            SELECT id 
            FROM campaign_recipients 
            WHERE campaign_id = :cid 
            AND status IN ('pending', 'chat_opened') 
            ORDER BY id ASC 
            LIMIT :maxCount
        ");
        $stmt->bindValue(':cid', $campaignId, PDO::PARAM_INT);
        $stmt->bindValue(':maxCount', $maxCount, PDO::PARAM_INT);
        $stmt->execute();
        $pendingRecipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($pendingRecipients)) {
            // Mark campaign completed
            Campaign::update($campaignId, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
            return [
                'success' => true,
                'sent_count' => 0,
                'completed' => true,
                'message' => 'All recipients in campaign have already been processed.',
            ];
        }

        // Update campaign status to in_progress
        Campaign::update($campaignId, [
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        $sentCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($pendingRecipients as $row) {
            $res = $this->sendRecipientDirect((int)$row['id']);
            if ($res['success']) {
                $sentCount++;
                $results[] = $res;
            } else {
                $failedCount++;
                $results[] = $res;
                if (str_contains($res['error'] ?? '', 'Daily messaging limit reached')) {
                    break; // Stop wave if daily quota reached
                }
            }
            // Small safety micro-throttle to protect network
            usleep(150000); // 150ms
        }

        // Check if more pending exist
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM campaign_recipients 
            WHERE campaign_id = :cid 
            AND status IN ('pending', 'chat_opened')
        ");
        $checkStmt->execute(['cid' => $campaignId]);
        $remainingCount = (int)$checkStmt->fetchColumn();

        $isCompleted = ($remainingCount === 0);
        if ($isCompleted) {
            Campaign::update($campaignId, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return [
            'success' => true,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'remaining_count' => $remainingCount,
            'completed' => $isCompleted,
            'results' => $results,
        ];
    }
}
