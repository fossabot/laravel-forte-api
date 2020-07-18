<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterFormRequest;
use App\Models\User;
use App\Models\UserItem;
use App\Models\XsollaUrl;
use App\Services\UserService;
use App\Services\XsollaAPIService;
use Auth;
use DB;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

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
    public function __construct(
        XsollaAPIService $xsollaAPI,
        UserService $userService
    ) {
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
     * @throws GuzzleException
     * @throws Throwable
     */
    public function login()
    {
        $socialite = \Socialite::driver('discord')->user();
        $user = $this->userService->discord($socialite->id);

        if (! $user) {
            $user = $this->store($socialite);
        } elseif ($user && $user->isDirty(['name', 'email'])) {
            $this->userService->update($user->id, [
                'name' => $socialite->name,
                'email' => $socialite->email,
            ]);
        }

        Auth::login($user);

        return redirect()->route('user.panel', $this->xsollaToken($user->id));
    }

    /**
     * 이용자를 추가(회원가입) 합니다.
     *
     * @param UserRegisterFormRequest $user
     * @return JsonResponse
     * @throws GuzzleException
     * @throws Throwable
     */
    public function store(UserRegisterFormRequest $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->userService->save((array) $user);

            $userData = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
            ];

            $this->xsollaAPI->request('POST', 'projects/:projectId/users', $userData);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            app(DiscordNotificationController::class)->exception($exception, (array) $user);
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
        $user = User::findOrfail($id);

        return new JsonResponse($user);
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
        $discordByUser = User::whereDiscordId($id)->firstOrFail();

        return new JsonResponse($discordByUser);
    }

    /**
     * 이용자의 정보를 갱신합니다.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
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
     * @throws GuzzleException
     */
    public function xsollaToken(int $id)
    {
        try {
            $user = User::findOrfail($id);

            if (! $user) {
                throw new NotFoundException('User NotFound');
            }

            $url = sprintf('https://%ssecure.xsolla.com/paystation2/?access_token=',
                config('app.env') !== 'production' && 'sandbox-');

            $xsollaBuildData = [
                'user' => [
                    'id' => [
                        'value' => (string) $id,
                    ],
                    'name' => [
                        'value' => $user->name,
                    ],
                ],
                'settings' => [
                    'project_id' => (int) config('xsolla.project_id'),
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

            $request = json_decode($this->xsollaAPI->request('POST', 'merchants/:merchantId/token', $xsollaBuildData), true);

            if (! $request['token']) {
                return view('xsolla.error');
            }

            XsollaUrl::create([
                XsollaUrl::TOKEN => $request['token'],
                XsollaUrl::USER_ID => $user->id,
                XsollaUrl::REDIRECT_URL => $url.$request['token'],
            ]);

            return $request['token'];
        } catch (Exception $e) {
            /** @var array $xsollaBuildData */
            app(DiscordNotificationController::class)->exception($e, $xsollaBuildData);
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
        $xsollaUrl = XsollaUrl::whereToken($token)->first();

        if (! $xsollaUrl) {
            return \Socialite::with('discord')->redirect();
        }

        $items = UserItem::whereUserId($xsollaUrl->user_id)
            ->with('items')
            ->get();

        return view('panel', ['items' => $items, 'token' => $xsollaUrl->token, 'redirect_url' => $xsollaUrl->redirect_url]);
    }
}
