<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Attachment;
use App\Models\MessageDraft;
use App\Services\ComposerService;

class ComposerController extends BaseController
{
    private ComposerService $composerService;

    public function __construct()
    {
        $this->composerService = new ComposerService();
    }

    public function index(Request $request): Response
    {
        $drafts = MessageDraft::where('is_archived', 0);
        $archivedDrafts = MessageDraft::where('is_archived', 1);

        return $this->render('composer/index', [
            'pageTitle'      => 'Message Composer & Drafts - WACM',
            'drafts'         => $drafts,
            'archivedDrafts' => $archivedDrafts,
        ], 'layouts/main');
    }

    public function create(Request $request): Response
    {
        return $this->render('composer/create', [
            'pageTitle' => 'Create Message Draft - WACM',
        ], 'layouts/main');
    }

    public function store(Request $request): Response
    {
        $validated = $this->validate($request, [
            'title' => 'required|min:3',
            'content' => 'required|min:5',
        ]);

        $user = Session::get('user');
        $file = $request->file('attachment');

        try {
            $draft = $this->composerService->createDraft($request->input(), $file, $user['id'] ?? null);
            Session::flash('success', "Draft '{$draft->title}' saved successfully.");
            return $this->redirect('/composer');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            return $this->redirect('/composer/create');
        }
    }

    public function edit(Request $request, string $id): Response
    {
        $draft = MessageDraft::find((int)$id);
        if (!$draft || $draft->is_archived) {
            Session::flash('error', 'Draft not found.');
            return $this->redirect('/composer');
        }

        $attachment = null;
        if ($draft->has_attachment) {
            $attachment = Attachment::getLatestForDraft((int)$draft->id);
        }

        return $this->render('composer/edit', [
            'pageTitle' => "Edit Draft: {$draft->title} - WACM",
            'draft' => $draft,
            'attachment' => $attachment,
        ], 'layouts/main');
    }

    public function update(Request $request, string $id): Response
    {
        $validated = $this->validate($request, [
            'title' => 'required|min:3',
            'content' => 'required|min:5',
        ]);

        $user = Session::get('user');
        $file = $request->file('attachment');

        try {
            $this->composerService->updateDraft((int)$id, $request->input(), $file, $user['id'] ?? null);
            Session::flash('success', 'Draft updated successfully.');
            return $this->redirect('/composer');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            return $this->redirect("/composer/{$id}/edit");
        }
    }

    public function duplicate(Request $request, string $id): Response
    {
        $user = Session::get('user');
        $newDraft = $this->composerService->duplicateDraft((int)$id, $user['id'] ?? null);

        if ($newDraft) {
            Session::flash('success', "Draft duplicated as '{$newDraft->title}'.");
        } else {
            Session::flash('error', 'Could not duplicate draft.');
        }

        return $this->redirect('/composer');
    }

    public function delete(Request $request, string $id): Response
    {
        MessageDraft::update((int)$id, ['is_archived' => 1]);
        Session::flash('success', 'Draft archived. You can restore it from the Archived section below.');
        return $this->redirect('/composer');
    }

    public function unarchive(Request $request, string $id): Response
    {
        $draft = MessageDraft::find((int)$id);
        if (!$draft) {
            Session::flash('error', 'Draft not found.');
            return $this->redirect('/composer');
        }
        MessageDraft::update((int)$id, ['is_archived' => 0]);
        Session::flash('success', "Draft '{$draft->title}' restored successfully.");
        return $this->redirect('/composer');
    }

    public function serveAttachment(Request $request, string $id): Response
    {
        $attachment = Attachment::find((int)$id);
        if (!$attachment || !file_exists($attachment->file_path)) {
            return new Response('Attachment not found', 404);
        }

        $content = file_get_contents($attachment->file_path);
        $response = new Response($content, 200);
        $response->header('Content-Type', $attachment->mime_type ?? 'image/png');
        $response->header('Content-Length', (string)strlen($content));
        $response->header('Cache-Control', 'public, max-age=86400');
        return $response;
    }
}
