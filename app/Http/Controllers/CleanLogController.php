<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RequestLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanLogController extends Controller
{
    /**
     *
     * @return mixed
     */
    public function clean()
    {
      $today = Carbon::now()->format('Y-m-d');
      $jsonLogs = [];
      $logs = RequestLog::where('created_at', '>', $today)->get();
      foreach($logs as $log) {
        $jsonLog = new \stdClass();
        $jsonLog->duration = $log->duration;
        $jsonLog->url = $log->url;
        $jsonLog->method = $log->method;
        $jsonLog->ip = $log->ip;
        $jsonLog->request = $log->request;
        $jsonLog->response = $log->response;
        array_push($jsonLogs, $jsonLog);
      }
      Storage::disk('log')->put('request/'.$today.'.log', json_encode($jsonLogs));
      RequestLog::where('created_at', '>', $today)->delete();


    }
}
