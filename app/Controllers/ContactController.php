<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Contact;
use App\Models\ContactList;
use App\Services\ContactService;

class ContactController extends BaseController
{
    private ContactService $contactService;

    public function __construct()
    {
        $this->contactService = new ContactService();
    }

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->query('search', ''),
            'group' => $request->query('group', ''),
            'consent_status' => $request->query('consent_status', ''),
            'opt_out' => $request->query('opt_out', ''),
            'list_id' => $request->query('list_id', ''),
        ];

        $contacts = $this->contactService->getContacts($filters);
        $groups = $this->contactService->getDistinctGroups();
        $lists = ContactList::where('is_archived', 0);

        return $this->render('contacts/index', [
            'pageTitle' => 'Contact Management - WACM',
            'contacts' => $contacts,
            'groups' => $groups,
            'lists' => $lists,
            'filters' => $filters,
        ], 'layouts/main');
    }

    public function create(Request $request): Response
    {
        $lists = ContactList::where('is_archived', 0);
        return $this->render('contacts/create', [
            'pageTitle' => 'Add Contact - WACM',
            'lists' => $lists,
        ], 'layouts/main');
    }

    public function store(Request $request): Response
    {
        $validated = $this->validate($request, [
            'full_name' => 'required|min:2',
            'phone_raw' => 'required|min:7',
        ]);

        $user = Session::get('user');
        $contact = $this->contactService->createContact($request->input(), $user['id'] ?? null);

        if (!$contact) {
            Session::flash('error', 'Failed to create contact.');
            return $this->redirect('/contacts/create');
        }

        Session::flash('success', "Contact '{$contact->full_name}' added successfully.");
        return $this->redirect('/contacts');
    }

    public function edit(Request $request, string $id): Response
    {
        $contact = Contact::find((int)$id);
        if (!$contact || $contact->is_deleted) {
            Session::flash('error', 'Contact not found.');
            return $this->redirect('/contacts');
        }

        $lists = ContactList::where('is_archived', 0);

        return $this->render('contacts/edit', [
            'pageTitle' => "Edit Contact: {$contact->full_name} - WACM",
            'contact' => $contact,
            'lists' => $lists,
        ], 'layouts/main');
    }

    public function update(Request $request, string $id): Response
    {
        $validated = $this->validate($request, [
            'full_name' => 'required|min:2',
            'phone_raw' => 'required|min:7',
        ]);

        $user = Session::get('user');
        $success = $this->contactService->updateContact((int)$id, $request->input(), $user['id'] ?? null);

        if (!$success) {
            Session::flash('error', 'Failed to update contact.');
            return $this->redirect("/contacts/{$id}/edit");
        }

        Session::flash('success', 'Contact updated successfully.');
        return $this->redirect('/contacts');
    }

    public function delete(Request $request, string $id): Response
    {
        $this->contactService->softDelete((int)$id);
        Session::flash('success', 'Contact has been removed.');
        return $this->redirect('/contacts');
    }

    public function export(Request $request): Response
    {
        $contacts = $this->contactService->getContacts();
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['ID', 'Full Name', 'Normalized Phone', 'Country Code', 'Group', 'Email', 'Consent Status', 'Opt Out Status', 'Notes', 'Created At']);

        foreach ($contacts as $c) {
            fputcsv($output, [
                $c->id,
                $c->full_name,
                $c->phone_normalized,
                $c->country_code,
                $c->group_name,
                $c->email,
                $c->consent_status,
                $c->opt_out_status ? 'Opted Out' : 'Active',
                $c->notes,
                $c->created_at,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wacm_contacts_' . date('Ymd_His') . '.csv"',
        ]);
    }
}
