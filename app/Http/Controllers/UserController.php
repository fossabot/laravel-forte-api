<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Socialite;
use App\Models\User;
use App\Models\Client;
use App\Models\XsollaUrl;
use Illuminate\Http\Request;
use App\Services\XsollaAPIService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserUpdateFormRequest;

class UserController extends Controller
{
    /**
     * @var XsollaAPIService
     */
    protected $xsollaAPI;

    /**
     * UserController constructor.
     * @param XsollaAPIService $xsollaAPI
     */
    public function __construct(XsollaAPIService $xsollaAPI)
    {
        $this->xsollaAPI = $xsollaAPI;
    }

    /**
     * 전체 이용자를 조회합니다.
     *
     * @return \Illuminate\Http\Response
     *
     * @SWG\Get(
     *     path="/users",
     *     description="List Users",
     *     produces={"application/json"},
     *     tags={"User"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="User Lists"
     *     ),
     * )
     */
    public function index()
    {
        return response()->json(User::scopeAllUsers());
    }

    /**
     * @return \Illuminate\Http\JsonResponse|string
     * @throws \Exception
     */
    public function login()
    {
        $discord_user = Socialite::with('discord')->user();
        if (empty($discord_user)){
            return redirect()->route('login');
        }
        $user = User::scopeGetUserByDiscordId($discord_user->id);
        if (empty($user)) {
            $this->store($discord_user);
        }
        $user = User::scopeGetUserByDiscordId($discord_user->id);
        if (Auth::loginUsingId($user->id)) {
            return redirect()->route('xsolla.short', $this->xsollaToken($user->id));
        } else {
            return redirect()->route('login');

        }
    }

    /**
     * 이용자를 추가(회원가입) 합니다.
     *
     * @param $discord_user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function store($discord_user)
    {
        DB::beginTransaction();
        try {
            $user = new User;
            $user->email = $discord_user->email;
            $user->name = $discord_user->name;
            $user->discord_id = $discord_user->id;
            $user->save();
            $datas = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
            ];

            $this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users', $datas);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $user,
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $discord_user->user);

            return response()->json([
                'error' => $exception,
            ], 400);
        }
    }

    /**
     * 이용자의 정보를 조회합니다.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     *
     * @SWG\Get(
     *     path="/users/{userId}",
     *     description="Show User Information",
     *     produces={"application/json"},
     *     tags={"User"},
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
     *         description="Successful User Information"
     *     ),
     * )
     */
    public function show(int $id)
    {
        return response()->json(User::scopeGetUser($id));
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
    public function discord(int $id)
    {
        return response()->json(User::scopeGetUserByDiscordId($id));
    }
    /**
     * 이용자의 정보를 갱신합니다.
     *
     * @param UserUpdateFormRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @SWG\Put(
     *     path="/users/{userId}",
     *     description="Update User Information",
     *     produces={"application/json"},
     *     tags={"User"},
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
     *         name="name",
     *         in="query",
     *         description="User Name",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="query",
     *         description="User Email",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="query",
     *         description="User Password",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Information Update"
     *     ),
     * )
     */
    public function update(UserUpdateFormRequest $request, int $id)
    {
        return response()->json(User::scopeUpdateUser($id, $request->all()));
    }

    /**
     * 이용자를 탈퇴처리합니다.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     *
     * @SWG\Delete(
     *     path="/users/{userId}",
     *     description="Destroy User",
     *     produces={"application/json"},
     *     tags={"User"},
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
     *         description="Successful Destroy User"
     *     ),
     * )
     */
    public function destroy(int $id)
    {
        return response()->json(User::scopeDestoryUser($id));
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function xsollaToken(int $id)
    {
        try {
            $user = User::scopeGetUser($id);
            if (! $user) {
                return response()->json([
                    'message' => 'User '.$id.' not found.',
                ], 404);
            }

            if (config('app.env') == 'production') {
                $mode = '';
            } else {
                $mode = 'sandbox-';
            }

            $url = 'https://'.$mode.'secure.xsolla.com/paystation2/?access_token=';

            $datas = [
                'user' => [
                    'id' => [
                        'value' => (string) $id,
                    ],
                    'name' => [
                        'value' => $user->name,
                    ],
                ],
                'settings' => [
                    'project_id' => (int) config('xsolla.projectId'),
//                    'mode' => $mode ? 'sandbox' : '', // server is actually deployed, remove its contents
                    'ui' => [
                        'theme' => 'default_dark',
                        'size' => 'large',
                        'components' => [
                            'virtual_items' => [
                                'selected_group' => 'forte',
                            ],
                        ],
                    ],
                ],
            ];

            $request = json_decode($this->xsollaAPI->requestAPI('POST', 'merchants/:merchantId/token', $datas), true);

            XsollaUrl::create([
                'token' => $request['token'],
                'user_id' => $user->id,
                'expired' => 0,
                'redirect_url' => $url.$request['token'],
                'hit' => 0,
            ]);
            return $request['token'];
        } catch (\Exception $exception) {
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $datas);

            return $exception->getMessage();
        }
    }

    public function shortXsollaURL(string $token)
    {
        $url = XsollaUrl::where('token', $token)->first();
        if($url->expired){
            return view('xsolla.short', ['token' => '', 'redirect_url' => '']);
        }
        return view('xsolla.short', ['token' => $url->token, 'redirect_url' => $url->redirect_url]);
    }
}
