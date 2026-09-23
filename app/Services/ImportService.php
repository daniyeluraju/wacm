<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\Contact;
use App\Models\ImportHistory;
use RuntimeException;

class ImportService
{
    /**
     * Parse CSV headers and preview rows
     */
    public function previewCsv(string $filePath, int $maxPreview = 5): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("Uploaded file could not be read.");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new RuntimeException("Could not open file.");
        }

        // Detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            fclose($handle);
            throw new RuntimeException("No readable headers found in file.");
        }

        // Clean headers (strip BOM, trim)
        $cleanHeaders = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$h));
        }, $headers);

        $previewRows = [];
        $totalRows = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count(array_filter($row)) === 0) continue;
            $totalRows++;
            if (count($previewRows) < $maxPreview) {
                $previewRows[] = array_map('trim', $row);
            }
        }

        fclose($handle);

        return [
            'headers' => $cleanHeaders,
            'preview_rows' => $previewRows,
            'total_rows' => $totalRows,
            'delimiter' => $delimiter,
        ];
    }

    /**
     * Process actual import with column mapping and comprehensive rejection tracking
     */
    public function processImport(string $filePath, array $mapping, string $delimiter = ',', string $defaultCountryCode = '+1', ?int $listId = null, ?int $userId = null): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new RuntimeException("File could not be opened for processing.");
        }

        $headers = fgetcsv($handle, 0, $delimiter);
        $importedCount = 0;
        $rejectedRows = [];
        $duplicateCount = 0;
        $rowNum = 1;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNum++;
                if (count(array_filter($row)) === 0) continue;

                $data = [];
                foreach ($mapping as $dbField => $colIndex) {
                    if ($colIndex !== '' && is_numeric($colIndex) && isset($row[(int)$colIndex])) {
                        $data[$dbField] = trim($row[(int)$colIndex]);
                    }
                }

                $name = $data['full_name'] ?? $data['name'] ?? '';
                $rawPhone = $data['phone_raw'] ?? $data['phone'] ?? '';
                $countryCode = !empty($data['country_code']) ? trim($data['country_code']) : $defaultCountryCode;

                // Validation 1: Missing phone number or unmapped column
                if (empty($rawPhone)) {
                    $rejectedRows[] = [
                        'row' => $rowNum,
                        'name' => $name ?: 'N/A',
                        'phone' => $rawPhone ?: '(Empty)',
                        'reason' => 'Missing phone number (Ensure the Phone column is mapped in Step 2)',
                    ];
                    continue;
                }

                $normalized = ContactService::normalizePhone($rawPhone, $countryCode);

                // Validation 2: Invalid phone format
                if (!ContactService::isValidPhone($normalized)) {
                    $rejectedRows[] = [
                        'row' => $rowNum,
                        'name' => $name ?: 'N/A',
                        'phone' => $rawPhone,
                        'reason' => "Invalid phone format: '{$normalized}' (Requires valid country code and 7-15 digits)",
                    ];
                    continue;
                }

                // Validation 3: Duplicate check
                $existing = Contact::findBy('phone_normalized', $normalized);
                if ($existing) {
                    $duplicateCount++;
                    if ($listId) {
                        (new ContactService())->assignToList((int)$existing->id, (int)$listId);
                    }
                    continue;
                }

                $consentStatus = $data['consent_status'] ?? 'unspecified';
                $email = !empty($data['email']) ? strtolower(trim($data['email'])) : null;
                $group = $data['group_name'] ?? null;

                $contact = Contact::create([
                    'full_name' => !empty($name) ? $name : 'Contact ' . substr($normalized, -4),
                    'phone_raw' => $rawPhone,
                    'country_code' => $countryCode,
                    'phone_normalized' => $normalized,
                    'email' => $email,
                    'group_name' => $group,
                    'consent_status' => in_array($consentStatus, ['granted', 'pending', 'denied', 'revoked', 'unspecified']) ? $consentStatus : 'unspecified',
                    'consent_source' => $data['consent_source'] ?? 'CSV Import',
                    'consent_date' => $consentStatus === 'granted' ? date('Y-m-d H:i:s') : null,
                    'opt_out_status' => !empty($data['opt_out_status']) ? 1 : 0,
                    'custom_field_1' => $data['custom_field_1'] ?? null,
                    'custom_field_2' => $data['custom_field_2'] ?? null,
                    'notes' => $data['notes'] ?? 'Imported via CSV',
                    'is_deleted' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                if ($contact) {
                    $importedCount++;

                    if ($consentStatus !== 'unspecified') {
                        ConsentRecord::create([
                            'contact_id' => $contact->id,
                            'status' => $consentStatus,
                            'source' => 'CSV Batch Import',
                            'notes' => 'Batch imported record',
                            'recorded_by' => $userId,
                            'recorded_at' => date('Y-m-d H:i:s'),
                        ]);
                    }

                    if ($listId) {
                        (new ContactService())->assignToList((int)$contact->id, (int)$listId);
                    }
                }
            }

            $pdo->commit();
            fclose($handle);

            // Generate error log CSV file if there are rejections
            $errorLogPath = null;
            if (!empty($rejectedRows)) {
                $exportDir = config('storage.paths.exports', dirname(__DIR__, 2) . '/storage/exports');
                if (!file_exists($exportDir)) mkdir($exportDir, 0755, true);

                $errorFileName = 'rejections_' . date('Ymd_His') . '_' . substr(md5($filePath), 0, 8) . '.csv';
                $errorLogPath = $exportDir . DIRECTORY_SEPARATOR . $errorFileName;

                $errFp = fopen($errorLogPath, 'w');
                fputcsv($errFp, ['Row Number', 'Name', 'Phone Number Provided', 'Rejection Reason']);
                foreach ($rejectedRows as $rej) {
                    fputcsv($errFp, [$rej['row'], $rej['name'], $rej['phone'], $rej['reason']]);
                }
                fclose($errFp);
            }

            // Record in Import History
            $history = ImportHistory::create([
                'user_id' => $userId,
                'file_name' => basename($filePath),
                'file_size' => filesize($filePath) ?: 0,
                'total_rows' => $importedCount + count($rejectedRows) + $duplicateCount,
                'imported_rows' => $importedCount,
                'rejected_rows' => count($rejectedRows),
                'error_log_path' => $errorLogPath ? basename($errorLogPath) : null,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            ActivityLog::log(
                'Contact Imported', 
                count($rejectedRows) > 0 && $importedCount === 0 ? 'Warning' : 'Success', 
                "Imported {$importedCount} contacts, {$duplicateCount} duplicates skipped, " . count($rejectedRows) . " rejected.", 
                null, 
                null
            );

            return [
                'success' => true,
                'imported' => $importedCount,
                'duplicates' => $duplicateCount,
                'rejected' => $rejectedRows,
                'error_log_file' => $errorLogPath ? basename($errorLogPath) : null,
                'history_id' => $history->id ?? null,
            ];

        } catch (\Throwable $e) {
            $pdo->rollBack();
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Generate sample CSV template content
     */
    public static function getSampleCsv(): string
    {
        $headers = ['full_name', 'phone_raw', 'country_code', 'group_name', 'email', 'consent_status', 'custom_field_1', 'custom_field_2', 'notes'];
        $rows = [
            ['Alice Johnson', '+1 (555) 234-5678', '+1', 'VIP Clients', 'alice@example.com', 'granted', 'Account #A102', 'Tier 1', 'Priority contact'],
            ['Robert Smith', '+44 7911 123456', '+44', 'New Leads', 'robert@example.co.uk', 'granted', 'Lead #849', 'Web Inquiry', 'Requested callback'],
            ['Maria Garcia', '+63 912 345 6789', '+63', 'Orientation 2026', 'maria@example.com', 'pending', 'Batch 4', 'Manila', 'Pending verification'],
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
