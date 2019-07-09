<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Receipt;
use Illuminate\Http\Request;
use App\Services\XsollaAPIService;

class PointController extends Controller
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

        if (! empty($user->withdraw_at)) {
            return response([
                'message' => 'Withdraw User Account',
            ], 400);
        }

        $oldPoints = $user->points;
        $user->points += $request->points;
        $user->save();

        $receipt = new Receipt;
        $receipt->user_id = $id;
        $receipt->client_id = Client::bringNameByToken($request->header('Authorization'))->id;
        $receipt->user_item_id = null;
        $receipt->about_cash = 0;
        $receipt->refund = 0;
        $receipt->points_old = $oldPoints;
        $receipt->points_new = $user->points;
        $receipt->save();

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
                'comment' => 'Updated User Point => '.Client::bringNameByToken($request->header('Authorization'))->name,
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

        return ['receipt_id' => $receipt->id];
    }
}
