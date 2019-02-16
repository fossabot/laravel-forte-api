<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Discord;

class DiscordController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index() {
        return response()->json(Discord::scopeAllDiscordAccounts());
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id) {
        return response()->json(Discord::scopeSelfDiscordAccount($id));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request, int $id) {
        $discordId = $request->only('discord_id');

        if (! User::scopeGetUser($id)) {
            return response([
                'message' => 'Not found User Id',
            ], 404);
        }

        if (Discord::scopeSelfDiscordAccount($discordId)) {
            return response([
                'message' => 'Duplicated Discord Account',
            ], 400);
        }

        Discord::create([
            'user_id' => $id,
            'discord_id' => $discordId,
        ]);

        return ['discord_id' => $discordId];
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id) {
        $discordId = $request->only('discord_id');

        if (! User::scopeGetUser($id)) {
            return response([
                'message' => 'Not found User Id',
            ], 404);
        }

        if (Discord::scopeSelfDiscordAccount($discordId)) {
            return response([
                'message' => 'Duplicated Discord Account',
            ], 400);
        }

        return response()->json(Discord::scopeUpdateDiscordAccount($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id) {
        return response()->json(Discord::scopeDestoryDiscordAccount($id));
    }
}
