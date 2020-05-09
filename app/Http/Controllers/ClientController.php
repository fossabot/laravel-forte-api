<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * @return void
     */
    public function renewal()
    {
        foreach (Client::get() as $client) {
            if (! in_array($client->name, Client::BOT_TOKEN_RENEWAL_EXCEPTION)) {
                Client::find($client->{Client::ID})->update([
                    Client::TOKEN => 'forte-'.$this->generateToken(),
                    Client::PREV_TOKEN => $client->{Client::PREV_TOKEN},
                ]);
            }
        }
    }

    /**
     * @return string
     */
    private function generateToken()
    {
        $merge = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        for ($i = 0; $i < 30; $i++) {
            $merge .= $characters[rand(0, strlen($characters) - 1)];
        }

        return Hash::make($merge);
    }

    /**
     * 이전 토큰으로 현재 토큰을 조회합니다.
     *
     * @return JsonResponse
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
    public function issue(): JsonResponse
    {
        $client = Client::where(Client::PREV_TOKEN, $_SERVER['HTTP_AUTHORIZATION'])->first() ?: null;

        if (! empty($client)) {
            return new JsonResponse([
                'token' => $client->{Client::TOKEN},
                'updated_at' => $client->{Client::UPDATED_AT},
            ]);
        }
    }
}
