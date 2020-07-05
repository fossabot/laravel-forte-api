<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Exception;
use NotificationChannels\Discord\Discord;

class DiscordNotificationController extends Controller
{
    const CHANNEL_ERROR = '555413130872750091';
    const CHANNEL_XSOLLA_SYNC = '561429015433445376';
    const CHANNEL_XSOLLA_USER_ACTION = '595192089457983490';
    const CHANNEL_USER_POINT_TRACKING = '648068498609799168';
    const CHANNEL_FORTE_DEPLOY = '576091937811988482';

    const DISCORD_CHANNELS = [
        self::CHANNEL_ERROR,
        self::CHANNEL_XSOLLA_SYNC,
        self::CHANNEL_XSOLLA_USER_ACTION,
        self::CHANNEL_USER_POINT_TRACKING,
        self::CHANNEL_FORTE_DEPLOY,
    ];

    /**
     * @param Exception $exception
     * @param array $data
     * @return array
     */
    public function exception(Exception $exception, array $data = [])
    {
        $params = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        ErrorLog::create([
            'environment' => config('app.env'),
            'title' => $exception->getFile().'('.$exception->getLine().')',
            'message' => $exception->getMessage(),
            'parameters' => $params,
        ]);

        return app(Discord::class)->send(self::CHANNEL_ERROR, [
            'content' => '['.config('app.env').'> '.now().'] API ERROR',
            'tts' => false,
            'embed' => [
                'title' => $exception->getFile().'('.$exception->getLine().')',
                'description' => "`ERROR` \n {$exception->getMessage()} \n `PARAMS` \n ``` {$params} ```",
            ],
        ]);
    }

    /**
     * @param int $count
     * @param array $data
     * @return array
     */
    public function sync(int $count, array $data = [])
    {
        $params = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        return app(Discord::class)->send(self::CHANNEL_XSOLLA_SYNC, [
            'content' => '['.config('app.env').'> '.now().'] Xsolla Sync',
            'tts' => false,
            'embed' => [
                'title' => 'Sync Information',
                'description' => "`COUNT` \n {$count} \n `ITEM SKU` \n ``` {$params} ```",
            ],
        ]);
    }

    /**
     * @return array
     */
    public function deploy()
    {
        return app(Discord::class)->send(self::CHANNEL_FORTE_DEPLOY, [
            'content' => 'AWS Auto Deploy Notification',
            'tts' => false,
            'embed' => [
                'title' => 'Deploy Information',
                'description' => 'Deploy ..',
            ],
        ]);
    }

    /**
     * @param string $action
     * @param array $data
     * @return mixed
     */
    public function xsollaUserAction(string $action, array $data = [])
    {
        $params = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        return app(Discord::class)->send(self::CHANNEL_XSOLLA_USER_ACTION, [
            'content' => now().'] Xsolla User Log',
            'tts' => false,
            'embed' => [
                'title' => $action,
                'description' => "``` {$params} ```",
            ],
        ]);
    }

    /**
     * @param string $email
     * @param int $discordId
     * @param int $deposit
     * @param int $point
     * @return
     */
    public function point(string $email, int $discordId, int $deposit, int $point)
    {
        return app(Discord::class)->send(self::CHANNEL_USER_POINT_TRACKING, [
            'content' => now().'] User Point Deposit Log',
            'tts' => false,
            'embed' => [
                'description' => "EMAIL: {$email} \n Discord ID: {$discordId} \n Deposit: {$deposit} \n User Point: {$point}",
            ],
        ]);
    }
}
