<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Receipt;
use App\Models\User;
use App\Services\XsollaAPIService;
use Illuminate\Http\Request;

class PointController extends Controller
{
    const MAX_POINT = 2000;

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
     * 스케쥴러에 의해 자동으로 스태프에게 MAX 포인트를 지급합니다.
     */
    public function schedule()
    {
        $staffs = User::scopeAllStaffs();

        foreach ($staffs as $staff) {
            $repetition = false;
            $needPoint = 0;

            $oldPoints = $staff->points;
            $staff->points += self::MAX_POINT;
            $staff->save();

            $receipt = Receipt::scopeCreateReceipt($staff->id, 5, null, 0, 0, $oldPoints, $staff->{User::POINTS}, 0);

            while (true) {
                $datas = [
                    'amount' => $repetition ? $needPoint : self::MAX_POINT,
                    'comment' => '팀 크레센도 STAFF 보상',
                    'project_id' => config('xsolla.projectKey'),
                    'user_id' => $receipt->{Receipt::USER_ID},
                ];

                $response = json_decode($this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$receipt->{Receipt::USER_ID}.'/recharge', $datas), true);

                if ($staff->points !== $response['amount']) {
                    $repetition = true;
                    $needPoint = $staff->points - $response['amount'];
                    continue;
                } else {
                    break;
                }
            }

            (new \App\Http\Controllers\DiscordNotificationController)->point($staff->email, $staff->discord_id, MAX_POINT, $staff->points);
        }
    }

    /**
     * 이용자에게 포인트를 지급합니다.
     *
     * @param Request $request
     * @param int $id
     * @return mixed
     *
     * @SWG\Post(
     *     path="/users/{userId}/points",
     *     description="Store(save) the User Points",
     *     produces={"application/json"},
     *     tags={"Point"},
     *      @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          description="Authorization Token",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="userId",
     *          in="path",
     *          description="User Id",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="points",
     *          in="query",
     *          description="Points",
     *          required=true,
     *          type="integer"
     *      ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Point provide"
     *     ),
     * )
     */
    public function store(Request $request, int $id)
    {
        $repetition = false;
        $needPoint = 0;
        $user = User::scopeGetUser($id);

        if (! $user) {
            return response([
                'message' => 'User does not exist',
            ], 404);
        }

        if (! empty($user->{User::DELETED_AT})) {
            return response([
                'message' => 'Withdraw User Account',
            ], 400);
        }

        $oldPoints = $user->{User::POINTS};
        $user->{User::POINTS} += $request->points;
        $user->save();

        $clientId = Client::bringNameByToken($request->header('Authorization'))->id;

        $receipt = Receipt::scopeCreateReceipt($id, $clientId, null, 0, 0, $oldPoints, $user->{User::POINTS}, 0);

        /*
         * @brief sync Xsolla DB from Crescendo API \n
         * 응답이 우리 DB와 일치하는 지 확인하고 \n
         * 일치하지 않는다면 이를 갱신하기 위해 API를 다시 호출 \n
         * (엑솔라 DB가 우리 DB의 데이터를 따르도록.. 만약 우리 DB는 500인데 엑솔라 DB 응답이 300이었다면 +200 요청을 다시 보내주는 식)
         * @author GBS-Skile
         */
        while (true) {
            $datas = [
                'amount' => $repetition ? $needPoint : $request->points,
                'comment' => '이용자 포인트 지급',
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

        (new \App\Http\Controllers\DiscordNotificationController)->point($user->{User::EMAIL}, $user->{User::DISCORD_ID}, $request->points, $user->{User::POINTS});

        return ['receipt_id' => $receipt->id];
    }
}
