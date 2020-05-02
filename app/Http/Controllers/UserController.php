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
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Socialite;

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
     * @return Response
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
     * @return JsonResponse|string
     * @throws Exception
     */
    public function login()
    {
        $socialite = Socialite::driver('discord')->user();
        $user = User::scopeGetUserByDiscordId($socialite->id);

        if (! $user) {
            $user = $this->store($socialite);
        } else if ($user && ($user->name !== $socialite->name)) {
            User::scopeUpdateUser($user->id, ['name' => $socialite->name]);
        }

        Auth::login($user);
        return redirect()->route('user.panel', $this->xsollaToken($user->id));
    }

    /**
     * 이용자를 추가(회원가입) 합니다.
     *
     * @param $user
     * @return ResponseFactory|Response
     * @throws Exception
     */
    public function store($user)
    {
        DB::beginTransaction();
        try {
            $user = User::scopeCreateUser($user);

            $datas = [
                'user_id' => $user->id,
                'user_name' => $user->{User::NAME},
                'email' => $user->{User::EMAIL},
            ];

            $this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users', $datas);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $user,
            ], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            (new DiscordNotificationController)->exception($exception, $user);

            return response()->json([
                'error' => $exception,
            ], 400);
        }
    }

    /**
     * 이용자의 정보를 조회합니다.
     *
     * @param int $id
     * @return Response
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
    public function discord(int $id)
    {
        return response()->json(User::scopeGetUserByDiscordId($id));
    }

    /**
     * 이용자의 정보를 갱신합니다.
     *
     * @param UserUpdateFormRequest $request
     * @param  int $id
     * @return Response
     * @throws Exception
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
     * @return Response
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
     * @return JsonResponse|string
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
                XsollaUrl::USER_ID => $user->id,
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
    public function attendances()
    {
        return response()->json(Attendance::scopeAttendances());
    }

    /**
     * 팀 크레센도 디스코드 이용자가 출석체크를 합니다.
     *
     * @param Request $request
     * @param string $id
     * @return void
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
    public function attendance(Request $request, string $id)
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

            return response()->json([
                'status' => 'success',
                'stack' => 1,
            ]);
        } else {
            $stackedAt = json_decode($attendance->{Attendance::STACKED_AT});
            $lastedAt = end($stackedAt);

            if ($lastedAt && Carbon::parse($lastedAt)->isToday()) {
                $diff = Carbon::now()->diff(Carbon::tomorrow())->format('%hh %im %ss');

                return response()->json([
                    'status' => 'exist_attendance',
                    'message' => 'exist today attend',
                    'diff' => $diff,
                ]);
            }

            if ($attendance->stack < 6) {
                array_push($stackedAt, Carbon::now()->toDateTimeString());

                $attendance->update([
                    Attendance::STACK => $attendance->{Attendance::STACK} + 1,
                    Attendance::STACKED_AT => json_encode($stackedAt),
                ]);

                return response()->json([
                    'status' => 'success',
                    Attendance::STACK => $attendance->{Attendance::STACK},
                ]);
            } else {
                $user = User::scopeGetUserByDiscordId($id);
                $repetition = false;
                $needPoint = 0;

                $deposit = ($request->isPremium > 0 ? rand(20, 30) : rand(10, 20));

                $oldPoints = $user->{User::POINTS};
                $user->{User::POINTS} += $deposit;
                $user->save();

                $receipt = Receipt::scopeCreateReceipt($user->id, 5, null, 0, 0, $oldPoints, $user->{User::POINTS}, 0);

                while (true) {
                    $datas = [
                        'amount' => $repetition ? $needPoint : $deposit,
                        'comment' => '포르테 출석체크 보상',
                        'project_id' => config('xsolla.projectKey'),
                        'user_id' => $receipt->{Receipt::USER_ID},
                    ];

                    $response = json_decode($this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$receipt->{Receipt::USER_ID}.'/recharge', $datas), true);

                    if ($user->{User::POINTS} !== $response['amount']) {
                        $repetition = true;
                        $needPoint = $user->{User::POINTS} - $response['amount'];
                        continue;
                    } else {
                        break;
                    }
                }

                (new DiscordNotificationController)->point($user->{User::EMAIL}, $user->{User::DISCORD_ID}, $deposit, $user->{User::POINTS});

                array_push($stackedAt, Carbon::now()->toDateTimeString());

                $attendance->update([
                    Attendance::STACK => 0,
                    Attendance::STACKED_AT => json_encode($stackedAt),
                ]);

                return response()->json([
                    'status' => 'regular',
                    'point' => $deposit,
                ]);
            }
        }
    }

    /**
     * 팀 크레센도 출석체크 랭킹을 불러옵니다.
     *
     * @return void
     *
     * @SWG\GET(
     *     path="/discords/attendances/ranks",
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
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Attendance Ranks"
     *     ),
     * )
     */
    public function attendanceRanks()
    {
        return response()->json(Attendance::scopeAttendanceRanks());
    }
}
