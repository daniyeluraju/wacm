<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Models\ContactList;
use App\Models\ImportHistory;
use App\Services\ImportService;

class ImportController extends BaseController
{
    private ImportService $importService;

    public function __construct()
    {
        $this->importService = new ImportService();
    }

    public function index(Request $request): Response
    {
        $lists = ContactList::where('is_archived', 0);
        $history = ImportHistory::all('id', 'DESC');
        $history = array_slice($history, 0, 10);

        $lastRejections = Session::get('_last_import_rejections', []);
        Session::remove('_last_import_rejections');

        return $this->render('import/index', [
            'pageTitle' => 'Import Contacts Wizard - WACM',
            'lists' => $lists,
            'history' => $history,
            'lastRejections' => $lastRejections,
        ], 'layouts/main');
    }

    public function upload(Request $request): Response
    {
        $file = $request->file('import_file');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid CSV or XLSX file to upload.');
            return $this->redirect('/import');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv' && $ext !== 'txt') {
            Session::flash('error', 'Currently standard CSV file imports are supported.');
            return $this->redirect('/import');
        }

        $tempDir = config('storage.paths.temporary', dirname(__DIR__, 2) . '/storage/temporary');
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

        $storedName = 'import_' . Security::randomString(16) . '.' . $ext;
        $destPath = $tempDir . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::flash('error', 'Failed to store uploaded file.');
            return $this->redirect('/import');
        }

        try {
            $preview = $this->importService->previewCsv($destPath);
            Session::set('_import_file_path', $destPath);
            Session::set('_import_file_name', $file['name']);
            Session::set('_import_list_id', $request->input('list_id'));

            return $this->render('import/preview', [
                'pageTitle' => 'Map Columns & Confirm Import - WACM',
                'preview' => $preview,
                'fileName' => $file['name'],
                'listId' => $request->input('list_id'),
            ], 'layouts/main');

        } catch (\Throwable $e) {
            @unlink($destPath);
            Session::flash('error', 'Error reading file: ' . $e->getMessage());
            return $this->redirect('/import');
        }
    }

    public function process(Request $request): Response
    {
        $filePath = Session::get('_import_file_path');
        if (!$filePath || !file_exists($filePath)) {
            Session::flash('error', 'Import session expired. Please upload your file again.');
            return $this->redirect('/import');
        }

        $mapping = $request->input('mapping', []);
        $delimiter = $request->input('delimiter', ',');
        $defaultCountryCode = $request->input('default_country_code', '+1');
        $listId = Session::get('_import_list_id');
        $user = Session::get('user');

        try {
            $result = $this->importService->processImport(
                $filePath,
                $mapping,
                $delimiter,
                $defaultCountryCode,
                $listId ? (int)$listId : null,
                $user['id'] ?? null
            );

            // Clean up temporary file
            @unlink($filePath);
            Session::remove('_import_file_path');
            Session::remove('_import_file_name');
            Session::remove('_import_list_id');

            if (!empty($result['rejected'])) {
                Session::set('_last_import_rejections', [
                    'rejected_rows' => $result['rejected'],
                    'imported' => $result['imported'],
                    'duplicates' => $result['duplicates'],
                    'error_file' => $result['error_log_file'],
                ]);

                if ($result['imported'] === 0) {
                    Session::flash('error', "Import finished with 0 contacts imported and " . count($result['rejected']) . " rejected rows. See rejection details below.");
                } else {
                    Session::flash('warning', "Import partially completed: {$result['imported']} imported, {$result['duplicates']} duplicates skipped, " . count($result['rejected']) . " rejected.");
                }
                return $this->redirect('/import');
            }

            Session::flash('success', "Import completed successfully! {$result['imported']} contacts imported ({$result['duplicates']} duplicates skipped).");
            return $this->redirect('/contacts');

        } catch (\Throwable $e) {
            Session::flash('error', 'Import processing failed: ' . $e->getMessage());
            return $this->redirect('/import');
        }
    }

    public function downloadSample(Request $request): Response
    {
        $csv = ImportService::getSampleCsv();
        return new Response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wacm_sample_import.csv"',
        ]);
    }

    public function downloadErrors(Request $request, string $filename): Response
    {
        $filename = basename($filename);
        $exportDir = config('storage.paths.exports', dirname(__DIR__, 2) . '/storage/exports');
        $filePath = $exportDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filePath)) {
            Session::flash('error', 'Rejection log file not found or expired.');
            return $this->redirect('/import');
        }

        $content = file_get_contents($filePath);
        return new Response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
