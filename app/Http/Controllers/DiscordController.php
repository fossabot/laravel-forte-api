<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Discord;

class DiscordController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/discords",
     *     description="List Discord Account Users",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Response(
     *         response=200,
     *         description="Discord Account User Lists"
     *     ),
     * )
     */
    public function index() {
        return response()->json(Discord::scopeAllDiscordAccounts());
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/discords/{discordId}",
     *     description="Show Discord Account User Information",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Parameter(
     *         name="discordId",
     *         in="path",
     *         description="Discord Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful Discord User Account Information"
     *     ),
     * )
     */
    public function show(int $id) {
        return response()->json(Discord::scopeSelfDiscordAccount($id));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     *
     * @SWG\Post(
     *     path="/users/{userId}/discord",
     *     description="Store(save) the User Discord Account Information",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *      @SWG\Parameter(
     *          name="userId",
     *          in="path",
     *          description="User Id",
     *          required=true,
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="discord_id",
     *          in="query",
     *          description="User Discord Id",
     *          required=true,
     *          type="string"
     *      ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Create User Discord Account Information"
     *     ),
     * )
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
     *
     * @SWG\Put(
     *     path="/users/{userId}/discord",
     *     description="Update User Discord Account Information",
     *     produces={"application/json"},
     *     tags={"User"},
     *      @SWG\Parameter(
     *          name="userId",
     *          in="path",
     *          description="User Id",
     *          required=true,
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="discord_id",
     *          in="query",
     *          description="User Discord Id",
     *          required=false,
     *          type="string"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Successful User Discord Account Information Update"
     *      ),
     * )
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
     *
     * @SWG\Delete(
     *     path="/users/{userId}/discord",
     *     description="Destroy User Discord Account",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful Destroy User Discord Account"
     *     ),
     * )
     */
    public function destroy(int $id) {
        return response()->json(Discord::scopeDestoryDiscordAccount($id));
    }
}
