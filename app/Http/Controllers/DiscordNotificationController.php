<?php

namespace App\Http\Controllers;

use App\ErrorLog;
use NotificationChannels\Discord\Discord;

class DiscordNotificationController extends Controller
{
    /**
     * @param \Exception $e
     * @param array $data
     * @return array
     */
    public function exception(\Exception $e, array $data = []) {
        $params = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        ErrorLog::create([
            'environment' => config('app.env'),
            'title' => $e->getFile() . '(' . $e->getLine() . ')',
            'message' => $e->getMessage(),
            'parameters' => $params,
        ]);

        return app(Discord::class)->send('555413130872750091', [
            'content' => '[' . config('app.env') . '> ' . now() . '] API ERROR',
            'tts' => false,
            'embed' => [
                'title' => $e->getFile() . '(' . $e->getLine() . ')',
                'description' => "`ERROR` \n {$e->getMessage()} \n `PARAMS` \n ``` {$params} ```"
            ]
        ]);
    }
}
