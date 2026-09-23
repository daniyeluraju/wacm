<?php

/**
 * WACM Full Application End-to-End Test Suite
 * Run with: php database/test_all_modules.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Autoloader::addNamespace('App', dirname(__DIR__) . '/app');

require_once dirname(__DIR__) . '/app/Helpers/functions.php';
require_once dirname(__DIR__) . '/app/Core/Env.php';
\App\Core\Env::load(dirname(__DIR__) . '/.env');

use App\Core\Database;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\MessageDraft;
use App\Models\StorageSetting;
use App\Services\AnalyticsService;
use App\Services\AssistantService;
use App\Services\CampaignService;
use App\Services\CleanupService;
use App\Services\ComposerService;
use App\Services\ContactService;
use App\Services\ImportService;
use App\Services\StorageUsageService;

echo "=======================================================\n";
echo "  WACM - Full Application End-to-End Verification\n";
echo "=======================================================\n\n";

$passed = 0;
$failed = 0;

function assertCheck(string $title, bool $condition, string $failReason = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$title} - {$failReason}\n";
        $failed++;
    }
}

// 1. Phone Normalization & Validation
$norm1 = ContactService::normalizePhone('(555) 234-5678', '+1');
assertCheck('Normalize US number with default code', $norm1 === '+15552345678');
$norm2 = ContactService::normalizePhone('+44 7911 123456');
assertCheck('Normalize international number with existing +', $norm2 === '+447911123456');
assertCheck('Valid phone validation', ContactService::isValidPhone($norm1) === true);

// 2. Contact Service CRUD & Consent Tracking
$contactService = new ContactService();
$contact = $contactService->createContact([
    'full_name' => 'Dr. Gregory House',
    'phone_raw' => '555-890-1234',
    'country_code' => '+1',
    'email' => 'gregory@princeton.local',
    'group_name' => 'Diagnostic VIP',
    'consent_status' => 'granted',
    'consent_source' => 'Registration Form',
], 1);
assertCheck('Create contact with E.164 normalization', $contact !== null && $contact->phone_normalized === '+15558901234');

// 3. Contact List Creation & Membership
$list = ContactList::create([
    'name' => 'Priority Case Roster ' . time(),
    'description' => 'Active clinical review cohort',
    'color' => '#10b981',
    'created_by' => 1,
    'is_archived' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);
assertCheck('Create Contact List', $list !== null);
$assigned = $contactService->assignToList((int)$contact->id, (int)$list->id);
assertCheck('Assign contact to list', $assigned === true);

// 4. Composer & Variable Placeholders
$composerService = new ComposerService();
$draft = $composerService->createDraft([
    'title' => 'Clinical Case Notification ' . time(),
    'content' => "Hello {{name}},\nYour record in {{group_name}} has been updated.\nYour phone is {{phone}}.",
    'caption' => null,
], null, 1);
assertCheck('Create Message Draft', $draft !== null);

$rendered = ComposerService::replaceVariables($draft->content, $contact->toArray());
assertCheck('Replace {{name}}, {{group_name}}, {{phone}} variables', str_contains($rendered, 'Dr. Gregory House') && str_contains($rendered, 'Diagnostic VIP') && str_contains($rendered, '+15558901234'));

// 5. Campaign Creation & Eligibility Calculation
$campaignService = new CampaignService();
$eligibility = $campaignService->calculateEligibility((int)$list->id, (int)$draft->id);
assertCheck('Calculate cohort eligibility stats', $eligibility['total'] >= 1 && $eligibility['eligible'] >= 1);

$campaign = $campaignService->createCampaign([
    'name' => 'Orientation Campaign ' . time(),
    'description' => 'Automated test campaign run',
    'contact_list_id' => $list->id,
    'message_draft_id' => $draft->id,
    'pacing_min_seconds' => 32,
    'pacing_max_seconds' => 40,
], 1);
assertCheck('Create Campaign and populate recipient queue', $campaign !== null);

$recipients = CampaignRecipient::where('campaign_id', $campaign->id);
$hasRecs = count($recipients) >= 1;
assertCheck('Verify populated campaign recipients', $hasRecs, 'Count is ' . count($recipients));

// 6. Manual WhatsApp Assistant Workflow
$assistantService = new AssistantService();
$nextRec = $assistantService->getNextRecipient((int)$campaign->id);
assertCheck('Assistant fetches next eligible recipient', $nextRec !== null && isset($nextRec['wa_url']));
assertCheck('Assistant WhatsApp URL format', str_contains($nextRec['wa_url'], 'web.whatsapp.com/send?phone='));

$recId = (int)$nextRec['recipient']['id'];
$assistantService->markChatOpened($recId);
$updatedRec = CampaignRecipient::find($recId);
assertCheck('Assistant marks chat opened', $updatedRec->status === 'chat_opened');

$assistantService->markCompleted($recId, 'Confirmed manual send in WhatsApp Web');
$completedRec = CampaignRecipient::find($recId);
assertCheck('Assistant marks user completion & checks campaign finish', $completedRec->status === 'user_marked_completed');

$campaignCheck = Campaign::find($campaign->id);
assertCheck('Campaign transitions to cleanup_pending on completion', $campaignCheck->status === 'cleanup_pending');

// 7. Storage Metrics & Cleanup Execution
$storageService = new StorageUsageService();
$metrics = $storageService->getStorageMetrics();
assertCheck('Storage usage calculation', isset($metrics['total_bytes']) && isset($metrics['breakdown']));

$cleanupService = new CleanupService();
$cleanupRes = $cleanupService->runCleanup('manual', (int)$campaign->id, 1);
assertCheck('Execute Cleanup and mark campaign as cleaned_up', $cleanupRes['success'] === true);

$cleanedCampaign = Campaign::find($campaign->id);
assertCheck('Campaign marked as cleaned_up', $cleanedCampaign->status === 'cleaned_up');

// 8. Analytics Data Aggregation
$analytics = (new AnalyticsService())->getDashboardMetrics();
assertCheck('Analytics metrics populated', $analytics['contacts']['total'] >= 1 && $analytics['campaigns']['total'] >= 1);

// 9. Clean up test records (in proper foreign key order)
Campaign::delete($campaign->id);
MessageDraft::delete($draft->id);
ContactList::delete($list->id);
Contact::delete($contact->id);

echo "\n=======================================================\n";
echo "  End-to-End Test Summary: {$passed} Passed, {$failed} Failed\n";
echo "=======================================================\n";
