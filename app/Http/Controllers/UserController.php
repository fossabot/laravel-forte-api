<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\XsollaUrl;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\XsollaAPIService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserUpdateFormRequest;
use App\Http\Requests\UserRegisterFormRequest;

class UserController extends Controller
{
    /**
     * @var XsollaAPIService
     */
    protected $xsollaAPI;
    /**
     * @var UserService
     */
    protected $us;

    /**
     * UserController constructor.
     * @param XsollaAPIService $xsollaAPI
     * @param UserService $us
     */
    public function __construct(XsollaAPIService $xsollaAPI, UserService $us)
    {
        $this->xsollaAPI = $xsollaAPI;
        $this->us = $us;
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
     * 이용자를 추가(회원가입) 합니다.
     *
     * @param UserRegisterFormRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     *
     * @SWG\Post(
     *     path="/users",
     *     description="Store(save) the User Information",
     *     produces={"application/json"},
     *     tags={"User"},
     *      @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          description="Authorization Token",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="name",
     *          in="query",
     *          description="User Name",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="email",
     *          in="query",
     *          description="User Email",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          in="query",
     *          description="User Password",
     *          required=true,
     *          type="string"
     *      ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Create User Information"
     *     ),
     * )
     */
    public function store(UserRegisterFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = new User;
            $user->email = $request->email;
            $user->name = $request->name;
            $user->password = bcrypt($request->password);
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
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $request->all());

            return response()->json([
                'error' => $exception,
            ], 400);
        }
    }

    /**
     * 이용자의 정보를 조회합니다.
     *
     * @param  int $id
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
     * 이용자의 엑솔라 상점 URL 을 발급받습니다.
     *
     * @param int $id
     * @return mixed|\Psr\Http\Message\ResponseInterface
     *
     * @SWG\GET(
     *     path="/users/{userId}/xsolla/token",
     *     description="Xsolla Shop Token",
     *     produces={"application/json"},
     *     tags={"Xsolla"},
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
     *         description="Successful Xsolla Shop Token"
     *     ),
     * )
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

            if (env('APP_ENV') == 'production') {
                $mode = '';
            } else {
                $mode = 'sandbox-secure.';
            }

            $url = 'https://'.$mode.'xsolla.com/paystation2/?access_token=';

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
                    'mode' => isset($mode) ? 'sandbox' : '',
                    'ui' => [
                        'theme' => 'default_dark',
                        'size' => 'large',
                        'components' => [
                            'virtual_items' => [
                                'selected_group' => Client::bringNameByToken(request()->header('Authorization'))->xsolla_selected_group_name,
                            ],
                        ],
                    ],
                ],
            ];

            $request = json_decode($this->xsollaAPI->requestAPI('POST', 'merchants/:merchantId/token', $datas), true);

            XsollaUrl::create([
                'token' => $request['token'],
                'redirect_url' => $url.$request['token'],
                'hit' => 0,
            ]);

            return response()->json([
                'url' => $url.$request['token'],
                'test_url' => route('xsolla.short', $request['token']),
            ], 200);
        } catch (\Exception $exception) {
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $datas);

            return $exception->getMessage();
        }
    }

    /**
     * 2FA 용 이용자 회원가입.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\POST(
     *     path="/users/{userId}/signin",
     *     description="User 2FA",
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
     *         name="password",
     *         in="query",
     *         description="User Password",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User 2FA Auth"
     *     ),
     * )
     */
    public function authentication(Request $request, int $id)
    {
        if (empty($id) || empty($request->password)) {
            return response()->json([
               'message' => 'Notfound',
            ], 404);
        }

        return $this->us->authentication($id, $request->password);
    }

    public function shortXsollaURL(string $token)
    {
        $url = XsollaUrl::where('token', $token)->first();

        return view('xsolla.short', ['token' => $url->token, 'redirect_url' => $url->redirect_url]);
    }
}
