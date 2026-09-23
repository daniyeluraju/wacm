<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\MessageDraft;
use RuntimeException;

class ComposerService
{
    /**
     * Replace template variables with actual contact values safely
     */
    public static function replaceVariables(string $template, array $contactData): string
    {
        $replacements = [
            '{{name}}' => $contactData['full_name'] ?? $contactData['name'] ?? 'Friend',
            '{{phone}}' => $contactData['phone_normalized'] ?? $contactData['phone_raw'] ?? '',
            '{{group_name}}' => $contactData['group_name'] ?? 'Members',
            '{{email}}' => $contactData['email'] ?? '',
            '{{custom_field_1}}' => $contactData['custom_field_1'] ?? '',
            '{{custom_field_2}}' => $contactData['custom_field_2'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Create message draft with optional attachment
     */
    public function createDraft(array $data, ?array $file = null, ?int $userId = null): ?MessageDraft
    {
        $hasAttachment = 0;
        $draft = MessageDraft::create([
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'has_attachment' => 0,
            'caption' => !empty($data['caption']) ? trim($data['caption']) : null,
            'created_by' => $userId,
            'is_archived' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($draft && !empty($file) && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $attachment = $this->handleUpload($file, (int)$draft->id, $userId);
            if ($attachment) {
                MessageDraft::update($draft->id, ['has_attachment' => 1]);
            }
        }

        if ($draft) {
            ActivityLog::log('Message Draft Created', 'Success', "Created draft '{$draft->title}'", null, null);
            AuditLog::log('Draft Created', 'MessageDraft', $draft->id);
        }

        return $draft;
    }

    /**
     * Update message draft
     */
    public function updateDraft(int $id, array $data, ?array $file = null, ?int $userId = null): bool
    {
        $draft = MessageDraft::find($id);
        if (!$draft) return false;

        $updateData = [
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'caption' => !empty($data['caption']) ? trim($data['caption']) : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($file) && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $this->handleUpload($file, $id, $userId);
            $updateData['has_attachment'] = 1;
        }

        $res = MessageDraft::update($id, $updateData);
        if ($res) {
            ActivityLog::log('Message Draft Updated', 'Success', "Updated draft '{$draft->title}'", null, null);
            AuditLog::log('Draft Updated', 'MessageDraft', $id);
        }
        return $res;
    }

    /**
     * Duplicate a message draft
     */
    public function duplicateDraft(int $id, ?int $userId = null): ?MessageDraft
    {
        $original = MessageDraft::find($id);
        if (!$original) return null;

        $newDraft = MessageDraft::create([
            'title' => $original->title . ' (Copy)',
            'content' => $original->content,
            'has_attachment' => $original->has_attachment,
            'caption' => $original->caption,
            'created_by' => $userId,
            'is_archived' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $newDraft;
    }

    /**
     * Handle secure image upload with strict validation
     */
    private function handleUpload(array $file, int $draftId, ?int $userId = null): ?Attachment
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if ($file['size'] > $maxSize) {
            throw new RuntimeException('File size exceeds the 10MB upload limit.');
        }

        // Verify MIME type with finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('Invalid image type. Only JPG, PNG, and WebP images are permitted.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            throw new RuntimeException('Invalid file extension.');
        }

        // Verify actual image structure and dimensions
        $imageSize = @getimagesize($file['tmp_name']);
        if (!$imageSize) {
            throw new RuntimeException('File is not a valid readable image.');
        }
        $dimensions = "{$imageSize[0]}x{$imageSize[1]}";

        // Generate safe random server-side filename
        $storedName = Security::randomString(24) . '.' . $ext;
        $uploadDir = config('storage.paths.uploads', dirname(__DIR__, 2) . '/storage/uploads');

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to securely store uploaded image.');
        }

        $attachment = Attachment::create([
            'draft_id' => $draftId,
            'original_name' => basename($file['name']),
            'stored_name' => $storedName,
            'file_path' => $destination,
            'file_size' => $file['size'],
            'mime_type' => $mime,
            'dimensions' => $dimensions,
            'is_temporary' => 0,
            'uploaded_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::log('Attachment Uploaded', 'Success', "Uploaded image {$storedName} ({$dimensions})", null, null);
        return $attachment;
    }
}
