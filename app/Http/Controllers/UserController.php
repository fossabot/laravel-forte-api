<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateFormRequest;
use App\Models\Attendance;
use App\Models\Receipt;
use App\Models\User;
use App\Models\UserItem;
use App\Models\XsollaUrl;
use App\Services\XsollaAPIService;
use Carbon\Carbon;
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
        return new JsonResponse(User::scopeAllUsers());
    }

    /**
     * @return JsonResponse|string
     * @throws Exception
     */
    public function login()
    {
        $socialite = Socialite::driver('discord')->user();
        $user = User::scopeGetUserByDiscordId($socialite->id);

        if (! $user) {
            $user = $this->store($socialite);
        } elseif ($user && ($user->name !== $socialite->name)) {
            User::scopeUpdateUser($user->{User::ID}, ['name' => $socialite->name]);
        }

        Auth::login($user);

        return redirect()->route('user.panel', $this->xsollaToken($user->{User::ID}));
    }

    /**
     * 이용자를 추가(회원가입) 합니다.
     *
     * @param $user
     * @return JsonResponse
     * @throws Exception
     */
    public function store($user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::scopeCreateUser($user);

            $datas = [
                'user_id' => $user->{User::ID},
                'user_name' => $user->{User::NAME},
                'email' => $user->{User::EMAIL},
            ];

            $this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users', $datas);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            (new DiscordNotificationController)->exception($exception, $user);

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
        return new JsonResponse(User::scopeGetUser($id));
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
        return new JsonResponse(User::scopeGetUserByDiscordId($id));
    }

    /**
     * 이용자의 정보를 갱신합니다.
     *
     * @param UserUpdateFormRequest $request
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
    public function update(UserUpdateFormRequest $request, int $id): JsonResponse
    {
        return new JsonResponse(User::scopeUpdateUser($id, $request->all()));
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
        return new JsonResponse(User::scopeDestoryUser($id));
    }

    /**
     * @param int $id
     * @return JsonResponse|string
     */
    public function xsollaToken(int $id)
    {
        try {
            $user = User::scopeGetUser($id);
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
        $items = UserItem::scopeUserItemLists($url->{XsollaUrl::USER_ID});

        return view('panel', ['items' => $items, 'token' => $url->{XsollaUrl::TOKEN}, 'redirect_url' => $url->{XsollaUrl::REDIRECT_URL}]);
    }

    /**
     * 팀 크레센도 출석체크를 불러옵니다.
     * @return mixed
     *
     * @SWG\GET(
     *     path="/discords/attendances",
     *     description="User Attendances",
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
     *         description="Successful User Attendances"
     *     ),
     * )
     */
    public function attendances(): JsonResponse
    {
        return new JsonResponse(Attendance::scopeAttendances());
    }

    /**
     * 팀 크레센도 디스코드 이용자가 출석체크를 합니다.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     *
     * @throws Exception
     * @SWG\POST(
     *     path="/discords/{discordId}/attendances",
     *     description="User Attendance",
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
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="isPremium",
     *         in="query",
     *         description="User Premium Role Check",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful User Attendance"
     *     ),
     * )
     */
    public function attendance(Request $request, string $id): JsonResponse
    {
        $attendance = Attendance::scopeExistAttendance($id);

        // init attend
        if (! $attendance) {
            $date = [Carbon::now()->toDateTimeString()];
            Attendance::insert([
                Attendance::DISCORD_ID => $id,
                Attendance::STACK => 1,
                Attendance::STACKED_AT => json_encode($date),
                Attendance::CREATED_AT => now(),
            ]);

            return new JsonResponse([
                'status' => 'success',
                'stack' => 1,
            ], Response::HTTP_CREATED);
        } else {
            $stackedAt = json_decode($attendance->{Attendance::STACKED_AT});
            $lastedAt = end($stackedAt);

            if ($lastedAt && Carbon::parse($lastedAt)->isToday()) {
                $diff = Carbon::now()->diff(Carbon::tomorrow())->format('%hh %im %ss');

                return new JsonResponse([
                    'status' => 'exist_attendance',
                    'message' => 'exist today attend',
                    'diff' => $diff,
                ], Response::HTTP_CONFLICT);
            }

            if ($attendance->stack < 6) {
                array_push($stackedAt, Carbon::now()->toDateTimeString());

                $attendance->update([
                    Attendance::STACK => $attendance->{Attendance::STACK} + 1,
                    Attendance::STACKED_AT => json_encode($stackedAt),
                ]);

                return new JsonResponse([
                    'status' => 'success',
                    Attendance::STACK => $attendance->{Attendance::STACK},
                ], Response::HTTP_OK);
            } else {
                $user = User::scopeGetUserByDiscordId($id);
                $deposit = ($request->isPremium > 0 ? rand(20, 30) : rand(10, 20));
                $oldPoints = $user->{User::POINTS};
                $user->{User::POINTS} += $deposit;
                $user->save();

                $receipt = Receipt::scopeCreateReceipt($user->{User::ID}, 5, null, 0, 0, $oldPoints, $user->{User::POINTS}, 0);

                (new PointController)->recharge($deposit, '포르테 출석체크 보상', $receipt->{Receipt::USER_ID});
                (new DiscordNotificationController)->point($user->{User::EMAIL}, $user->{User::DISCORD_ID}, $deposit, $user->{User::POINTS});

                array_push($stackedAt, Carbon::now()->toDateTimeString());

                $attendance->update([
                    Attendance::STACK => 0,
                    Attendance::STACKED_AT => json_encode($stackedAt),
                ]);

                return new JsonResponse([
                    'status' => 'regular',
                    'point' => $deposit,
                ], Response::HTTP_OK);
            }
        }
    }
}
