<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\ContactListMember;
use PDO;

class ContactService
{
    /**
     * Normalize international phone number to clean standard E.164 without symbols
     */
    public static function normalizePhone(string $phone, string $defaultCountryCode = '+91'): string
    {
        $raw = trim($phone);
        // Remove spaces, hyphens, parentheses, dots, brackets
        $cleaned = preg_replace('/[^\d+]/', '', $raw);

        if (empty($cleaned)) {
            return '';
        }

        // If starts with +, keep it and return clean digits
        if (str_starts_with($cleaned, '+')) {
            $digitsOnly = preg_replace('/\D/', '', $cleaned);
            return '+' . $digitsOnly;
        }

        // If starts with 00 (international call prefix), replace with +
        if (str_starts_with($cleaned, '00')) {
            return '+' . substr($cleaned, 2);
        }

        $codeClean = ltrim($defaultCountryCode, '+');

        // Check if raw numbers already start with the clean country code (e.g. 919876543210 for India or 14155552671 for US)
        if (!empty($codeClean) && str_starts_with($cleaned, $codeClean) && strlen($cleaned) >= (strlen($codeClean) + 7)) {
            return '+' . $cleaned;
        }

        // Otherwise strip leading zeros and append default country code
        $digits = ltrim($cleaned, '0');
        $code = '+' . (empty($codeClean) ? '91' : $codeClean);
        return $code . $digits;
    }

    /**
     * Validate if a phone number is valid format
     */
    public static function isValidPhone(string $phone): bool
    {
        $normalized = self::normalizePhone($phone);
        // E.164 numbers are usually 7 to 15 digits plus leading +
        return (bool) preg_match('/^\+[1-9]\d{6,14}$/', $normalized);
    }

    /**
     * Search and filter contacts
     */
    public function getContacts(array $filters = []): array
    {
        $pdo = Database::connection();
        $conditions = ['c.is_deleted = 0'];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(c.full_name LIKE :search OR c.phone_normalized LIKE :search OR c.email LIKE :search OR c.group_name LIKE :search)';
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['group'])) {
            $conditions[] = 'c.group_name = :group';
            $params['group'] = $filters['group'];
        }

        if (!empty($filters['consent_status'])) {
            $conditions[] = 'c.consent_status = :consent';
            $params['consent'] = $filters['consent_status'];
        }

        if (isset($filters['opt_out']) && $filters['opt_out'] !== '') {
            $conditions[] = 'c.opt_out_status = :opt_out';
            $params['opt_out'] = (int) $filters['opt_out'];
        }

        if (!empty($filters['list_id'])) {
            $conditions[] = 'c.id IN (SELECT contact_id FROM contact_list_members WHERE list_id = :list_id)';
            $params['list_id'] = (int) $filters['list_id'];
        }

        $whereSql = implode(' AND ', $conditions);
        $sql = "SELECT c.* FROM contacts c WHERE {$whereSql} ORDER BY c.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new Contact($row);
        }
        return $results;
    }

    /**
     * Create contact with consent tracking
     */
    public function createContact(array $data, ?int $userId = null): ?Contact
    {
        $countryCode = $data['country_code'] ?? '+1';
        $normalized = self::normalizePhone($data['phone_raw'] ?? '', $countryCode);

        $contact = Contact::create([
            'full_name' => trim($data['full_name'] ?? ''),
            'phone_raw' => trim($data['phone_raw'] ?? ''),
            'country_code' => $countryCode,
            'phone_normalized' => $normalized,
            'email' => !empty($data['email']) ? strtolower(trim($data['email'])) : null,
            'group_name' => !empty($data['group_name']) ? trim($data['group_name']) : null,
            'consent_status' => $data['consent_status'] ?? 'unspecified',
            'consent_source' => $data['consent_source'] ?? 'Direct Entry',
            'consent_date' => !empty($data['consent_status']) && $data['consent_status'] === 'granted' ? date('Y-m-d H:i:s') : null,
            'opt_out_status' => !empty($data['opt_out_status']) ? 1 : 0,
            'opt_out_date' => !empty($data['opt_out_status']) ? date('Y-m-d H:i:s') : null,
            'custom_field_1' => $data['custom_field_1'] ?? null,
            'custom_field_2' => $data['custom_field_2'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($contact) {
            // Record consent trail
            if (!empty($data['consent_status']) && $data['consent_status'] !== 'unspecified') {
                ConsentRecord::create([
                    'contact_id' => $contact->id,
                    'status' => $data['consent_status'],
                    'source' => $data['consent_source'] ?? 'Manual Admin Entry',
                    'notes' => $data['notes'] ?? 'Initial contact record',
                    'recorded_by' => $userId,
                    'recorded_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Assign to contact list if specified
            if (!empty($data['list_id'])) {
                $this->assignToList((int)$contact->id, (int)$data['list_id']);
            }

            ActivityLog::log('Contact Created', 'Success', "Added contact {$contact->full_name} ({$contact->phone_normalized})", null, (int)$contact->id);
            AuditLog::log('Contact Created', 'Contact', $contact->id, null, $contact->toArray());
        }

        return $contact;
    }

    /**
     * Update existing contact
     */
    public function updateContact(int $id, array $data, ?int $userId = null): bool
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return false;
        }

        $oldValues = $contact->toArray();
        $countryCode = $data['country_code'] ?? $contact->country_code;
        $normalized = self::normalizePhone($data['phone_raw'] ?? $contact->phone_raw, $countryCode);

        $updateData = [
            'full_name' => trim($data['full_name'] ?? $contact->full_name),
            'phone_raw' => trim($data['phone_raw'] ?? $contact->phone_raw),
            'country_code' => $countryCode,
            'phone_normalized' => $normalized,
            'email' => !empty($data['email']) ? strtolower(trim($data['email'])) : null,
            'group_name' => !empty($data['group_name']) ? trim($data['group_name']) : null,
            'consent_status' => $data['consent_status'] ?? $contact->consent_status,
            'consent_source' => $data['consent_source'] ?? $contact->consent_source,
            'opt_out_status' => isset($data['opt_out_status']) ? (int)$data['opt_out_status'] : $contact->opt_out_status,
            'custom_field_1' => $data['custom_field_1'] ?? $contact->custom_field_1,
            'custom_field_2' => $data['custom_field_2'] ?? $contact->custom_field_2,
            'notes' => $data['notes'] ?? $contact->notes,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // If opt out status toggled to 1, set opt_out_date
        if (!empty($updateData['opt_out_status']) && empty($oldValues['opt_out_status'])) {
            $updateData['opt_out_date'] = date('Y-m-d H:i:s');
            ActivityLog::log('Opt-Out Recorded', 'Success', "Contact {$contact->full_name} opted out", null, $id);
        }

        // If consent status changed, record in consent_records
        if ($updateData['consent_status'] !== $contact->consent_status) {
            ConsentRecord::create([
                'contact_id' => $id,
                'status' => $updateData['consent_status'],
                'source' => $updateData['consent_source'] ?? 'Profile Update',
                'notes' => $updateData['notes'] ?? 'Consent status updated',
                'recorded_by' => $userId,
                'recorded_at' => date('Y-m-d H:i:s'),
            ]);
            ActivityLog::log('Consent Recorded', 'Success', "Consent updated to {$updateData['consent_status']}", null, $id);
        }

        $res = Contact::update($id, $updateData);
        if ($res) {
            AuditLog::log('Contact Updated', 'Contact', $id, $oldValues, $updateData);
        }
        return $res;
    }

    /**
     * Soft delete contact
     */
    public function softDelete(int $id): bool
    {
        $contact = Contact::find($id);
        if (!$contact) return false;

        $res = Contact::update($id, [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::log('Contact Deleted', 'Success', "Soft-deleted contact ID {$id}", null, $id);
        AuditLog::log('Contact Soft-Deleted', 'Contact', $id);
        return $res;
    }

    /**
     * Assign contact to list
     */
    public function assignToList(int $contactId, int $listId): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("INSERT IGNORE INTO contact_list_members (list_id, contact_id, added_at) VALUES (:lid, :cid, NOW())");
        return $stmt->execute(['lid' => $listId, 'cid' => $contactId]);
    }

    /**
     * Get distinct groups
     */
    public function getDistinctGroups(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query("SELECT DISTINCT group_name FROM contacts WHERE group_name IS NOT NULL AND group_name != '' AND is_deleted = 0 ORDER BY group_name ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
