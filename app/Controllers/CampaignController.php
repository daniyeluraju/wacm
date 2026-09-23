<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\MessageDraft;
use App\Services\CampaignService;

class CampaignController extends BaseController
{
    private CampaignService $campaignService;

    public function __construct()
    {
        $this->campaignService = new CampaignService();
    }

    public function index(Request $request): Response
    {
        $campaigns = Campaign::all('id', 'DESC');

        return $this->render('campaigns/index', [
            'pageTitle' => 'Campaign Management - WACM',
            'campaigns' => $campaigns,
        ], 'layouts/main');
    }

    public function create(Request $request): Response
    {
        $lists = ContactList::where('is_archived', 0);
        $drafts = MessageDraft::where('is_archived', 0);
        $activeContactCount = Contact::count(['is_deleted' => 0]);

        return $this->render('campaigns/create', [
            'pageTitle' => 'Create New Campaign - WACM',
            'lists' => $lists,
            'drafts' => $drafts,
            'activeContactCount' => $activeContactCount,
        ], 'layouts/main');
    }

    public function store(Request $request): Response
    {
        $validated = $this->validate($request, [
            'name' => 'required',
            'contact_list_id' => 'required',
            'message_draft_id' => 'required',
        ]);

        $user = Session::get('user');
        $campaign = $this->campaignService->createCampaign($request->input(), $user['id'] ?? null);

        if (!$campaign) {
            Session::flash('error', 'Failed to initialize campaign.');
            return $this->redirect('/campaigns/create');
        }

        Session::flash('success', "Campaign '{$campaign->name}' created and ready for review.");
        return $this->redirect("/campaigns/{$campaign->id}");
    }

    public function show(Request $request, string $id): Response
    {
        $campaign = Campaign::find((int)$id);
        if (!$campaign) {
            Session::flash('error', 'Campaign not found.');
            return $this->redirect('/campaigns');
        }

        $list = ContactList::find((int)$campaign->contact_list_id);
        $draft = MessageDraft::find((int)$campaign->message_draft_id);

        $recipients = CampaignRecipient::where('campaign_id', $campaign->id);

        $stats = [
            'total' => count($recipients),
            'completed' => 0,
            'chat_opened' => 0,
            'pending' => 0,
            'skipped' => 0,
            'opted_out' => 0,
            'invalid' => 0,
        ];

        foreach ($recipients as $r) {
            if ($r->status === 'user_marked_completed') $stats['completed']++;
            elseif ($r->status === 'chat_opened') $stats['chat_opened']++;
            elseif ($r->status === 'skipped') $stats['skipped']++;
            elseif ($r->status === 'opted_out') $stats['opted_out']++;
            elseif ($r->status === 'invalid') $stats['invalid']++;
            else $stats['pending']++;
        }

        return $this->render('campaigns/show', [
            'pageTitle' => "Campaign: {$campaign->name} - WACM",
            'campaign' => $campaign,
            'list' => $list,
            'draft' => $draft,
            'recipients' => $recipients,
            'stats' => $stats,
        ], 'layouts/main');
    }

    public function start(Request $request, string $id): Response
    {
        $this->campaignService->startCampaign((int)$id);
        Session::flash('success', 'Campaign started! Launching WhatsApp Assistant.');
        return $this->redirect("/assistant/{$id}");
    }

    public function pause(Request $request, string $id): Response
    {
        $this->campaignService->pauseCampaign((int)$id);
        Session::flash('info', 'Campaign paused.');
        return $this->redirect("/campaigns/{$id}");
    }

    public function directSend(Request $request, string $id): Response
    {
        $cloudService = new \App\Services\WhatsAppCloudApiService();
        $res = $cloudService->runAutomatedCampaignWave((int)$id);

        if ($request->isAjax()) {
            return $this->json($res);
        }

        if ($res['success']) {
            Session::flash('success', "Direct automated dispatch complete! {$res['sent_count']} message(s) processed.");
        } else {
            Session::flash('error', $res['error'] ?? 'Direct dispatch failed.');
        }

        return $this->redirect("/campaigns/{$id}");
    }

    public function cancel(Request $request, string $id): Response
    {
        $this->campaignService->cancelCampaign((int)$id);
        Session::flash('warning', 'Campaign cancelled.');
        return $this->redirect("/campaigns/{$id}");
    }

    public function destroy(Request $request, string $id): Response
    {
        $campaign = Campaign::find((int)$id);
        if (!$campaign) {
            Session::flash('error', 'Campaign not found.');
            return $this->redirect('/campaigns');
        }

        $name = $campaign->name;

        // Delete related recipients first (foreign-key safety)
        CampaignRecipient::raw(
            'DELETE FROM `campaign_recipients` WHERE `campaign_id` = :cid',
            ['cid' => (int)$id]
        );

        // Hard-delete the campaign
        Campaign::delete((int)$id);

        Session::flash('success', "Campaign \"{$name}\" has been permanently deleted.");
        return $this->redirect('/campaigns');
    }

    public function refreshMessages(Request $request, string $id): Response
    {
        $campaign = Campaign::find((int)$id);
        if (!$campaign) {
            Session::flash('error', 'Campaign not found.');
            return $this->redirect('/campaigns');
        }

        $draft = MessageDraft::find((int)$campaign->message_draft_id);
        if (!$draft) {
            Session::flash('error', 'Message draft not found.');
            return $this->redirect("/campaigns/{$id}");
        }

        // Re-generate prepared_message for all pending/chat_opened recipients
        $pdo = \App\Core\Database::connection();
        $stmt = $pdo->prepare("
            SELECT cr.id, c.full_name, c.phone_normalized, c.email, c.custom_field_1, c.custom_field_2
            FROM campaign_recipients cr
            JOIN contacts c ON cr.contact_id = c.id
            WHERE cr.campaign_id = :cid
            AND cr.status IN ('pending', 'chat_opened')
        ");
        $stmt->execute(['cid' => (int)$id]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($rows as $row) {
            $personalized = \App\Services\ComposerService::replaceVariables($draft->content, $row);
            $upd = $pdo->prepare("
                UPDATE campaign_recipients
                SET prepared_message = :msg, updated_at = NOW()
                WHERE id = :rid
            ");
            $upd->execute(['msg' => $personalized, 'rid' => $row['id']]);
            $count++;
        }

        Session::flash('success', "✅ Messages refreshed! {$count} recipient message(s) updated with the latest draft content.");
        return $this->redirect("/campaigns/{$id}");
    }
}
