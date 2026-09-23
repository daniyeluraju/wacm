<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Campaign;
use App\Models\CleanupHistory;
use PDO;

class CleanupService
{
    /**
     * Run storage and temporary file cleanup
     */
    public function runCleanup(string $type = 'manual', ?int $campaignId = null, ?int $userId = null): array
    {
        $tempDir = config('storage.paths.temporary', dirname(__DIR__, 2) . '/storage/temporary');
        $exportDir = config('storage.paths.exports', dirname(__DIR__, 2) . '/storage/exports');
        $retentionHours = (int) config('storage.cleanup.retention_hours', 24);
        $cutoffTime = time() - ($retentionHours * 3600);

        $filesDeleted = 0;
        $bytesReleased = 0;
        $recordsDeleted = 0;

        // 1. Delete temporary import files older than retention policy
        foreach ([$tempDir, $exportDir] as $dir) {
            if (!file_exists($dir)) continue;
            $files = scandir($dir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..' || $f === '.gitkeep') continue;
                $path = $dir . DIRECTORY_SEPARATOR . $f;
                if (is_file($path)) {
                    if (filemtime($path) <= $cutoffTime || $type === 'manual_force') {
                        $size = filesize($path) ?: 0;
                        if (@unlink($path)) {
                            $filesDeleted++;
                            $bytesReleased += $size;
                        }
                    }
                }
            }
        }

        // 2. Mark completed campaigns as cleaned_up if all temporary assets cleared
        if ($campaignId) {
            Campaign::update($campaignId, [
                'status' => 'cleaned_up',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $pdo = Database::connection();
            $stmt = $pdo->query("SELECT id FROM campaigns WHERE status = 'cleanup_pending'");
            while ($cid = $stmt->fetchColumn()) {
                Campaign::update((int)$cid, [
                    'status' => 'cleaned_up',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 3. Record in Cleanup History table
        $history = CleanupHistory::create([
            'campaign_id' => $campaignId,
            'initiated_by' => $userId,
            'cleanup_type' => in_array($type, ['automatic', 'manual', 'scheduled']) ? $type : 'manual',
            'records_deleted' => $recordsDeleted,
            'files_deleted' => $filesDeleted,
            'storage_released_bytes' => $bytesReleased,
            'status' => 'completed',
            'error_message' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::log('Cleanup Completed', 'Success', "Storage cleanup finished: {$filesDeleted} files deleted, " . format_bytes($bytesReleased) . " released.", $campaignId, null);

        return [
            'success' => true,
            'files_deleted' => $filesDeleted,
            'bytes_released' => $bytesReleased,
            'bytes_formatted' => format_bytes($bytesReleased),
            'history_id' => $history->id ?? null,
        ];
    }
}
