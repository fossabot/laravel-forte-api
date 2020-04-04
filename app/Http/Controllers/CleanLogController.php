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
    protected $file;

    /**
     * @var string
     */
    protected $yesterday;
    /**
     * @var BackupService
     */
    protected $backupService;

    /**
     * CleanLogController constructor.
     * @param Filesystem $file
     * @param BackupService $backupService
     */
    public function __construct(FileSystem $file, BackupService $backupService)
    {
        $this->file = $file;
        $this->backupService = $backupService;
        $this->yesterday = Carbon::yesterday()->format('Y-m-d');
    }

    /**
     * @param array $logs
     */
    public function clean($logs = [])
    {
        $requestLogs = RequestLog::where('created_at', '<', $this->yesterday)->get();

        foreach ($requestLogs as $log) {
            array_push($logs, [
                'duration' => $log->duration,
                'url' => $log->url,
                'method' => $log->method,
                'ip' => $log->ip,
                'request' => $log->request,
                'response' => $log->response,
            ]);
        }

        if (! file_exists(storage_path('requests'))) {
            mkdir(storage_path('requests'), 0777);
        }

        $this->file->put(storage_path('requests/'.$this->yesterday.'.log'), json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        RequestLog::scopeClearRequestLogs($this->yesterday);

        $this->backupService->request();
    }
}
