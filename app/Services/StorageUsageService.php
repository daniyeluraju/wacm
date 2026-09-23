<?php

namespace App\Services;

class StorageUsageService
{
    /**
     * Compute comprehensive storage usage statistics
     */
    public function getStorageMetrics(): array
    {
        $storagePaths = [
            'uploads' => config('storage.paths.uploads', dirname(__DIR__, 2) . '/storage/uploads'),
            'temporary' => config('storage.paths.temporary', dirname(__DIR__, 2) . '/storage/temporary'),
            'exports' => config('storage.paths.exports', dirname(__DIR__, 2) . '/storage/exports'),
            'logs' => config('storage.paths.logs', dirname(__DIR__, 2) . '/storage/logs'),
        ];

        $breakdown = [];
        $totalBytes = 0;

        foreach ($storagePaths as $key => $dir) {
            $stats = $this->getDirectorySize($dir);
            $breakdown[$key] = [
                'path' => $dir,
                'bytes' => $stats['bytes'],
                'formatted' => format_bytes($stats['bytes']),
                'files_count' => $stats['count'],
            ];
            $totalBytes += $stats['bytes'];
        }

        $warningThresholdMb = (int) config('storage.cleanup.warning_threshold_mb', 500);
        $totalMb = round($totalBytes / (1024 * 1024), 2);

        return [
            'total_bytes' => $totalBytes,
            'total_formatted' => format_bytes($totalBytes),
            'total_mb' => $totalMb,
            'warning_threshold_mb' => $warningThresholdMb,
            'is_warning' => $totalMb >= $warningThresholdMb,
            'breakdown' => $breakdown,
        ];
    }

    private function getDirectorySize(string $dir): array
    {
        $size = 0;
        $count = 0;

        if (!file_exists($dir) || !is_dir($dir)) {
            return ['bytes' => 0, 'count' => 0];
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_file($path)) {
                $size += filesize($path);
                $count++;
            }
        }

        return ['bytes' => $size, 'count' => $count];
    }
}
