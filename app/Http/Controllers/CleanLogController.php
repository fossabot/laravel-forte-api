<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;

class CleanLogController extends Controller
{
    /**
     * @var Filesystem
     */
    protected Filesystem $file;

    /**
     * @var string
     */
    protected string $yesterday;
    /**
     * @var BackupService
     */
    protected BackupService $backupService;

    /**
     * CleanLogController constructor.
     * @param Filesystem $file
     * @param BackupService $backupService
     */
    public function __construct(
        FileSystem $file,
        BackupService $backupService
    ) {
        $this->file = $file;
        $this->backupService = $backupService;
        $this->yesterday = Carbon::yesterday()->format('Y-m-d');
    }

    public function clean()
    {
        $logs = $this->getYesterdaysLogs();

        if ($this->isRequestsCacheDirectoryNotExists())
        {
            $this->createRequestCacheDirectory();
        }

        $this->saveLogsToStorage($logs);
        $this->backupService->request();
    }

    public function getYesterdaysLogs($logs = []): array
    {
        $logs = RequestLog::select(['duration', 'url', 'method', 'ip', 'request', 'response'])
            ->where('created_at', '<', $this->yesterday)
            ->get()
            ->toArray();

        return $logs;
    }

    private function isRequestsCacheDirectoryNotExists(): bool
    {
        return !file_exists(storage_path('requests'));
    }

    private function createRequestCacheDirectory(): void
    {
        mkdir(storage_path('requests'), 0777);
    }

    private function saveLogsToStorage($logs): void
    {
        $this->file->put(storage_path('requests/'.$this->yesterday.'.log'), json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
