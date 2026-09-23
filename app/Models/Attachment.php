<?php

namespace App\Models;

use App\Core\Model;

class Attachment extends Model
{
    protected string $table = 'attachments';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'draft_id',
        'original_name',
        'stored_name',
        'file_path',
        'file_size',
        'mime_type',
        'dimensions',
        'is_temporary',
        'uploaded_by',
        'created_at',
    ];

    public static function getLatestForDraft(int $draftId): ?self
    {
        $stmt = self::db()->prepare("SELECT * FROM `attachments` WHERE `draft_id` = :did ORDER BY `id` DESC LIMIT 1");
        $stmt->execute(['did' => $draftId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;
        return new self($row);
    }
}
