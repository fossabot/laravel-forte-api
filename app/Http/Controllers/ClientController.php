<?php

namespace App\Http\Controllers;

use App\Client;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * @return array
     */
    public function renewal()
    {
        foreach (Client::get() as $client) {
            if (! in_array($client->name, Client::BOT_TOKEN_RENEWAL_EXCEPTION)) {
                Client::find($client->id)->update([
                    'token' => 'forte-'.Hash::make(date('Y-m-d h:m:s')),
                    'prev_token' => $client->token,
                ]);
            }
        }

        return (new \App\Http\Controllers\DiscordNotificationController)->clientToken();
    }

    /**
     * 이전 토큰으로 현재 토큰을 조회합니다.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/clients/token",
     *     description="",
     *     produces={"application/json"},
     *     tags={"Token"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description=""
     *     ),
     * )
     */
    public function issue()
    {
        $client = Client::where('prev_token', $_SERVER['HTTP_AUTHORIZATION'])->first() ?: null;
        if (! empty($client)) {
            return response()->json([
               'token' => $client->token,
               'updated_at' => $client->updated_at,
            ]);
        }
    }
}
