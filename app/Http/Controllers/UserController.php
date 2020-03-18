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
use Cassandra\Date;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        if (empty($discord_user)) {
            return redirect()->route('login');
        }
        $user = User::scopeGetUserByDiscordId($discord_user->id);
        if (empty($user)) {
            $this->store($discord_user);
        }
        $user = User::scopeGetUserByDiscordId($discord_user->id);
        if (Auth::loginUsingId($user->id)) {
            return redirect()->route('user.panel', $this->xsollaToken($user->id));
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

    /**
     * 2020. 03. 15
     * shops/{token} 과 inventory 를 panel 로 합침
     * panel 에서 포르테 상점과 인벤토리를 이용할 수 있도록.
     *
     * @param string $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function panel(string $token)
    {
        $url = XsollaUrl::where('token', $token)->first();
        $items = UserItem::scopeUserItemLists($url->user_id);

        if ($url->expired) {
            $url->token = $url->redirect_url = '';
        }

        return view('panel', ['items' => $items, 'token' => $url->token, 'redirect_url' => $url->redirect_url]);
    }

    /**
     * 팀 크레센도 디스코드 이용자가 출석체크를 합니다.
     *
     * @param Request $request
     * @param string $id
     * @return void
     *
     * @throws \Exception
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
                'discord_id' => $id,
                'stack' => 1,
                'stacked_at' => json_encode($date),
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'stack' => 1,
            ]);
        } else {
            $stackedAt = json_decode($attendance->stacked_at);

            if ($attendance->stack < 6) {
                $lastedAt = end($stackedAt);
                $now = new DateTime();

                if ($lastedAt && $now->format('Y-m-d') === date_format(date_create($lastedAt), 'Y-m-d')) {
                    $tomorrow = new DateTime(date('Y-m-d', strtotime('+1 days')).' 00:00:00');
                    $data = $now->diff($tomorrow);
                    $diff = $data->format('%hh %im %ss');

                    return response()->json([
                        'status' => 'exist_attendance',
                        'message' => 'exist today attend',
                        'diff' => $diff,
                    ]);
                } else {
                    array_push($stackedAt, Carbon::now()->toDateTimeString());

                    $attendance->update([
                        'stack' => $attendance->stack + 1,
                        'stacked_at' => json_encode($stackedAt),
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'stack' => $attendance->stack,
                    ]);
                }
            } else {
                $user = User::scopeGetUserByDiscordId($id);
                $repetition = false;
                $needPoint = 0;

                $deposit = ($request->isPremium > 0 ? rand(20, 30) : rand(10, 20));

                $oldPoints = $user->points;
                $user->points += $deposit;
                $user->save();

                $receipt = new Receipt;
                $receipt->user_id = $user->id;
                $receipt->client_id = 5; // Lara
                $receipt->user_item_id = null;
                $receipt->about_cash = 0;
                $receipt->refund = 0;
                $receipt->points_old = $oldPoints;
                $receipt->points_new = $user->points;
                $receipt->save();

                while (true) {
                    $datas = [
                        'amount' => $repetition ? $needPoint : $deposit,
                        'comment' => '포르테 출석체크 보상',
                        'project_id' => env('XSOLLA_PROJECT_KEY'),
                        'user_id' => $receipt->user_id,
                    ];

                    $response = json_decode($this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$receipt->user_id.'/recharge', $datas), true);

                    if ($user->points !== $response['amount']) {
                        $repetition = true;
                        $needPoint = $user->points - $response['amount'];
                        continue;
                    } else {
                        break;
                    }
                }

                (new \App\Http\Controllers\DiscordNotificationController)->point($user->email, $user->discord_id, $deposit, $user->points);

                array_push($stackedAt, Carbon::now()->toDateTimeString());

                $attendance->update([
                    'stack' => 0,
                    'stacked_at' => json_encode($stackedAt),
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
