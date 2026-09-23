<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Campaign;
use App\Services\AssistantService;

class AssistantController extends BaseController
{
    private AssistantService $assistantService;

    public function __construct()
    {
        $this->assistantService = new AssistantService();
    }

    public function index(Request $request): Response
    {
        $activeCampaigns = Campaign::where('status', 'in_progress');
        $readyCampaigns = Campaign::where('status', 'ready_for_review');

        return $this->render('assistant/index', [
            'pageTitle' => 'WhatsApp Web Assistant - WACM',
            'activeCampaigns' => $activeCampaigns,
            'readyCampaigns' => $readyCampaigns,
        ], 'layouts/main');
    }

    public function session(Request $request, string $campaignId): Response
    {
        $campaign = Campaign::find((int)$campaignId);
        if (!$campaign) {
            Session::flash('error', 'Campaign not found.');
            return $this->redirect('/assistant');
        }

        $sessionData = $this->assistantService->getNextRecipient((int)$campaignId);

        return $this->render('assistant/session', [
            'pageTitle' => "Assistant: {$campaign->name} - WACM",
            'campaign' => $campaign,
            'sessionData' => $sessionData,
        ], 'layouts/main');
    }

    public function markOpened(Request $request, string $recipientId): Response
    {
        $this->assistantService->markChatOpened((int)$recipientId);
        return $this->json(['success' => true]);
    }

    public function markCompleted(Request $request, string $recipientId): Response
    {
        $notes = $request->input('notes', '');
        $campaignId = $request->input('campaign_id');

        $this->assistantService->markCompleted((int)$recipientId, $notes);

        if ($request->isAjax()) {
            return $this->json(['success' => true]);
        }

        Session::flash('success', 'Recipient marked as completed.');
        return $this->redirect("/assistant/{$campaignId}");
    }

    public function skip(Request $request, string $recipientId): Response
    {
        $reason = $request->input('reason', 'User skipped');
        $campaignId = $request->input('campaign_id');

        $this->assistantService->skipRecipient((int)$recipientId, $reason);

        if ($request->isAjax()) {
            return $this->json(['success' => true]);
        }

        Session::flash('info', 'Recipient skipped.');
        return $this->redirect("/assistant/{$campaignId}");
    }

    public function sendDirect(Request $request, string $recipientId): Response
    {
        $campaignId = $request->input('campaign_id');
        $cloudService = new \App\Services\WhatsAppCloudApiService();
        $res = $cloudService->sendRecipientDirect((int)$recipientId);

        if ($request->isAjax()) {
            return $this->json($res);
        }

        if ($res['success']) {
            Session::flash('success', 'Message sent directly with media!');
        } else {
            Session::flash('error', $res['error'] ?? 'Direct dispatch failed.');
        }

        return $this->redirect("/assistant/{$campaignId}");
    }
}
