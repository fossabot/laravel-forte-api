<?php

namespace App\Http\Controllers;

use App\User;
use App\Discord;
use Illuminate\Http\Request;

class DiscordController extends Controller
{
    /**
     * 모든 디스코드 계정을 조회합니다.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/discords",
     *     description="List Discord Account Users",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Discord Account User Lists"
     *     ),
     * )
     */
    public function index()
    {
        return response()->json(Discord::scopeAllDiscordAccounts());
    }

    /**
     * 디스코드 아이디로 조회합니다.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/discords/{discordId}",
     *     description="Show Discord Account User Information",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
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
    public function show(int $id)
    {
        return response()->json(Discord::scopeSelfDiscordAccount($id));
    }

    /**
     * 이용자의 디스코드 계정을 연동합니다.
     *
     * @param Request $request
     * @param int $id
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     *
     * @SWG\Post(
     *     path="/users/{userId}/discord",
     *     description="Store(save) the User Discord Account Information",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="discord_id",
     *         in="query",
     *         description="User Discord Id",
     *         required=true,
     *         type="string"
     *     ),
     *    @SWG\Response(
     *        response=201,
     *        description="Successful Create User Discord Account Information"
     *    ),
     * )
     */
    public function store(Request $request, int $id)
    {
        if (! User::scopeGetUser($id)) {
            return response([
                'message' => 'Not found User Id',
            ], 404);
        }

        if (isset($request->discord_id)) {
            if (! empty(User::scopeGetUser($id)->withdraw_at)) {
                return response([
                    'message' => 'Withdraw User Account',
                ], 400);
            }

            if (Discord::scopeSelfDiscordSelectFieldAccount('user_id', $id)) {
                return response([
                    'message' => 'Duplicated User Id or Discord Account',
                ], 400);
            }

            if (Discord::scopeSelfDiscordAccount($request->discord_id)) {
                return response([
                    'message' => 'Duplicated Discord Account',
                ], 400);
            }
        }

        Discord::create([
            'user_id' => $id,
            'discord_id' => $request->discord_id,
        ]);

        return response()->json([
            'discord_id' => $request->discord_id,
        ], 201);
    }

    /**
     * 이용자의 디스코드 계정 정보를 갱신합니다.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     *
     * @SWG\Put(
     *     path="/users/{userId}/discord",
     *     description="Update User Discord Account Information",
     *     produces={"application/json"},
     *     tags={"Discord"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="discord_id",
     *         in="query",
     *         description="User Discord Id",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Discord Account Information Update"
     *     ),
     * )
     */
    public function update(Request $request, int $id)
    {
        if (! User::scopeGetUser($id)) {
            return response([
                'message' => 'Not found User Id',
            ], 404);
        }

        if (isset($request->discord_id)) {
            if (Discord::scopeSelfDiscordAccount($request->discord_id)) {
                return response([
                    'message' => 'Duplicated Discord Account',
                ], 400);
            }
        } else {
            return response([
                'message' => 'Not found User discord_id',
            ], 404);
        }

        return response()->json(Discord::scopeUpdateDiscordAccount($id, $request->all()));
    }

    /**
     * 이용자에게 연동된 디스코드 계정 정보를 제거합니다.
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
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
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
    public function destroy(int $id)
    {
        return response()->json(Discord::scopeDestoryDiscordAccount($id));
    }
}
