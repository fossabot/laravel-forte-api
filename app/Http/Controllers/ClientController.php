<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Hash;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    /**
     * @return void
     */
    public function renewal()
    {
        foreach (Client::get() as $client) {
            if ($client->isRenewable()) {
                $this->renewToken($client);
            }
        }
    }

    private function renewToken($client)
    {
        $client->newToken = $this->generateToken();
        $client->save();
    }

    /**
     * @return string
     */
    private function generateToken()
    {
        return Hash::make(Str::Random(40));
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
        $client = Client::wherePrevToken($_SERVER['HTTP_AUTHORIZATION'])->first() ?: null;

        if (! empty($client)) {
            return new JsonResponse([
                Client::TOKEN => $client->token,
                Client::UPDATED_AT => $client->updated_at,
            ]);
        }

        return new JsonResponse([]);
    }
}
