<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterFormRequest;
use App\Models\User;
use App\Models\XsollaUrl;
use App\Services\UserService;
use App\Services\XsollaAPIService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * @var XsollaAPIService
     */
    protected $xsollaAPI;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserController constructor.
     * @param XsollaAPIService $xsollaAPI
     * @param UserService $userService
     */
    public function __construct(XsollaAPIService $xsollaAPI,
                                UserService $userService)
    {
        $this->xsollaAPI = $xsollaAPI;
        $this->userService = $userService;
    }

    /**
     * 전체 이용자를 조회합니다.
     *
     * @return JsonResponse
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
    public function index(): JsonResponse
    {
        return new JsonResponse($this->userService->index());
    }

    /**
     * @return JsonResponse|string
     * @throws Exception
     */
    public function login()
    {
        $socialite = Socialite::driver('discord')->user();
        $user = $this->userService->discord($socialite->id);

        if (! $user) {
            $user = $this->store($socialite);
        } elseif ($user && ($user->name !== $socialite->name)) {
            $this->userService->update($user->{User::ID}, ['name' => $socialite->name]);
        }

        Auth::login($user);

        return redirect()->route('user.panel', $this->xsollaToken($user->{User::ID}));
    }

    /**
     * 이용자를 추가(회원가입) 합니다.
     *
     * @param UserRegisterFormRequest $user
     * @param UserService $userService
     * @return JsonResponse
     * @throws Exception
     */
    public function store(UserRegisterFormRequest $user, UserService $userService): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $userService->save((array) $user);

            $datas = [
                'user_id' => $user->{User::ID},
                'user_name' => $user->{User::NAME},
                'email' => $user->{User::EMAIL},
            ];

            $this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users', $datas);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            (new DiscordNotificationController)->exception($exception, (array) $user);

            return new JsonResponse([
                'error' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => 'success',
            'data' => $user,
        ], Response::HTTP_CREATED);
    }

    /**
     * 이용자의 정보를 조회합니다.
     *
     * @param int $id
     * @return JsonResponse
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
    public function show(int $id): JsonResponse
    {
        return new JsonResponse($this->userService->show($id));
    }

    /**
     * 디스코드 아이디로 조회합니다.
     *
     * @param int $id
     * @return JsonResponse
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
    public function discord(int $id): JsonResponse
    {
        return new JsonResponse($this->userService->discord($id));
    }

    /**
     * 이용자의 정보를 갱신합니다.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @SWG\Patch(
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
    public function update(Request $request, int $id): JsonResponse
    {
        return new JsonResponse($this->userService->update($id, $request->all()));
    }

    /**
     * 이용자를 탈퇴처리합니다.
     *
     * @param int $id
     * @return JsonResponse
     *
     * @throws Exception
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
    public function destroy(int $id): JsonResponse
    {
        return new JsonResponse($this->userService->destroy($id));
    }

    /**
     * @param int $id
     * @return JsonResponse|string
     */
    public function xsollaToken(int $id)
    {
        try {
            $user = $this->userService->show($id);
            if (! $user) {
                return new JsonResponse([
                    'message' => 'User '.$id.' not found.',
                ], Response::HTTP_NOT_FOUND);
            }

            $mode = '';
            if (config('app.env') !== 'production') {
                $mode = 'sandbox-';
            }

            $url = 'https://'.$mode.'secure.xsolla.com/paystation2/?access_token=';

            $datas = [
                'user' => [
                    'id' => [
                        'value' => (string) $id,
                    ],
                    'name' => [
                        'value' => $user->{User::NAME},
                    ],
                ],
                'settings' => [
                    'project_id' => (int) config('xsolla.projectId'),
                    //                    'mode' => $mode ? 'sandbox' : '', // server is actually deployed, remove its contents
                    'ui' => [
                        'theme' => 'default',
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

            if (! $request['token']) {
                return view('xsolla.error');
            }

            XsollaUrl::create([
                XsollaUrl::TOKEN => $request['token'],
                XsollaUrl::USER_ID => $user->{User::ID},
                XsollaUrl::REDIRECT_URL => $url.$request['token'],
            ]);

            return $request['token'];
        } catch (Exception $exception) {
            /** @var array $datas */
            (new DiscordNotificationController)->exception($exception, $datas);

            return $exception->getMessage();
        }
    }

    /**
     * 2020. 03. 15
     * shops/{token} 과 inventory 를 panel 로 합침
     * panel 에서 포르테 상점과 인벤토리를 이용할 수 있도록.
     *
     * @param string $token
     * @return Factory|View
     */
    public function panel(string $token)
    {
        $url = XsollaUrl::where(XsollaUrl::TOKEN, $token)->first();
        $items = $this->userService->items($url->{XsollaUrl::USER_ID});

        return view('panel', ['items' => $items, 'token' => $url->{XsollaUrl::TOKEN}, 'redirect_url' => $url->{XsollaUrl::REDIRECT_URL}]);
    }
}
