<?php

namespace App\Http\Controllers;

use App\Exceptions\MessageException;
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
    const CHANNEL_FORTE = '467604242589548564';

    /**
     * @param Exception $exception
     * @param array $data
     * @return MessageException
     * @throws MessageException
     */
    public function exception(Exception $exception, array $data = []): MessageException
    {
        $params = $this->convertArrayToJson($data);

        ErrorLog::create([
            'environment' => config('app.env'),
            'title' => $exception->getFile().'('.$exception->getLine().')',
            'message' => $exception->getMessage(),
            'parameters' => $params,
        ]);

        app(Discord::class)->send(self::CHANNEL_ERROR, [
            'content' => '['.config('app.env').'> '.now().'] API ERROR',
            'tts' => false,
            'embed' => [
                'title' => $exception->getFile().'('.$exception->getLine().')',
                'description' => "`ERROR` \n {$exception->getMessage()} \n `PARAMS` \n ``` {$params} ```",
            ],
        ]);

        throw new MessageException($exception->getMessage());
    }

    /**
     * @param array $data
     * @return array
     */
    public function sync(array $data = []): array
    {
        $params = $this->convertArrayToJson($data);

        return app(Discord::class)->send(self::CHANNEL_XSOLLA_SYNC, [
            'content' => '['.config('app.env').'> '.now().'] Xsolla Sync',
            'tts' => false,
            'embed' => [
                'title' => 'Sync Information',
                'description' => "`ITEM SKU` \n ``` {$params} ```",
            ],
        ]);
    }

    /**
     * @return array
     */
    public function deploy(): array
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
     * @return array
     */
    public function xsollaUserAction(string $action, array $data = []): array
    {
        $params = $this->convertArrayToJson($data);

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
     * @return array
     */
    public function point(string $email, int $discordId, int $deposit, int $point): array
    {
        return app(Discord::class)->send(self::CHANNEL_USER_POINT_TRACKING, [
            'content' => now().'] User Point Deposit Log',
            'tts' => false,
            'embed' => [
                'description' => "EMAIL: {$email} \n Discord ID: {$discordId} \n Deposit: {$deposit} \n User Point: {$point}",
            ],
        ]);
    }

    /**
     * @param string $message
     * @return array
     */
    public function message(string $message): array
    {
        return app(Discord::class)->send(self::CHANNEL_FORTE, [
            'content' => now().'] Forte API Message',
            'tts' => false,
            'embed' => [
                'description' => sprintf('%s', $message),
            ],
        ]);
    }

    /**
     * @param array $params
     * @return string
     */
    private function convertArrayToJson(array $params): string
    {
        return json_encode($params,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_NUMERIC_CHECK
        );
    }
}
