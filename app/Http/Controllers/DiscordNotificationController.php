<?php

namespace App\Http\Controllers;

use App\Client;
use App\ErrorLog;
use NotificationChannels\Discord\Discord;

class DiscordNotificationController extends Controller
{
    /**
     * @param \Exception $exception
     * @param array $data
     * @return array
     */
    public function exception(\Exception $exception, array $data = []) {
        $params = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        ErrorLog::create([
            'environment' => config('app.env'),
            'title' => $exception->getFile() . '(' . $exception->getLine() . ')',
            'message' => $exception->getMessage(),
            'parameters' => $params,
        ]);

        return app(Discord::class)->send('555413130872750091', [
            'content' => '[' . config('app.env') . '> ' . now() . '] API ERROR',
            'tts' => false,
            'embed' => [
                'title' => $exception->getFile() . '(' . $e->getLine() . ')',
                'description' => "`ERROR` \n {$e->getMessage()} \n `PARAMS` \n ``` {$params} ```"
            ]
        ]);
    }

    /**
     * @param int $count
     * @param array $data
     * @return array
     */
    public function sync(int $count, array $data = []) {
        $params = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        return app(Discord::class)->send('561429015433445376', [
            'content' => '[' . config('app.env') . '> ' . now() . '] Xsolla Sync',
            'tts' => false,
            'embed' => [
                'title' => 'Sync Information',
                'description' => "`COUNT` \n {$count} \n `ITEM SKU` \n ``` {$params} ```"
            ]
        ]);
    }

    /**
     * @param string $status
     * @return array
     */
    public function deploy(string $status) {
        $time = now();

        if ($status == 'starting') {
            $status = '배포를 시작했습니다.';
        } elseif ($status == 'finish') {
            $status = '배포가 종료되었습니다.';
        } else {
            $status = '에러가 발생했습니다.';
        }

        return app(Discord::class)->send('576091937811988482', [
            'content' => 'AWS Auto Deploy Notification',
            'tts' => false,
            'embed' => [
                'title' => 'Deploy Information',
                'description' => "`TIME` \n {$time} \n `STATUS` \n {$status}"
            ]
        ]);
    }

    /**
     * @return array
     */
    public function clientToken() {
        $name = [];

        foreach (Client::get() as $client) {
            if (! in_array($client->name,Client::BOT_TOKEN_RENEWAL_EXCEPTION)) {
                array_push($name, $client->name);
            }
        }

        $name = implode(', ', $name);

        return app(Discord::class)->send('561429015433445376', [
            'content' => now() . '] Client Token Issue (' . date('H') . '/24)',
            'tts' => false,
            'embed' => [
                'title' => 'Client Information',
                'description' => "`Clients` \n {$name}"
            ]
        ]);
    }
}
