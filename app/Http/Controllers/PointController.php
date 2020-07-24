<?php

namespace App\Http\Controllers;

use App\Jobs\XsollaRechargeJob;
use App\Models\Client;
use App\Models\Receipt;
use App\Models\User;
use App\Services\XsollaAPIService;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Queue;

class PointController extends Controller
{
    private const STAFF_TYPE = 2;
    private const MAX_POINT = 2000;

    /**
     * @var XsollaAPIService
     */
    protected XsollaAPIService $xsollaAPI;

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
        $users = User::ofType(self::STAFF_TYPE)->get();

        foreach ($users as $user) {
            $oldPoints = $user->points;
            $user->points += self::MAX_POINT;
            $user->save();

            Receipt::store($user->id, 5, null, 0, 0, $oldPoints, $user->points, 0);

            Queue::pushOn('xsolla-recharge', new XsollaRechargeJob($user, self::MAX_POINT, '스태프 포인트 지급'));
        }

        app(DiscordNotificationController::class)->message('스태프 포인트 일괄 지급 완료');
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
    public function store(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user || $user->trashed()) {
            throw new NotFoundException('User does not exist or withdraw User');
        }

        $oldPoints = $user->points;
        $user->points += $request->input('point');
        $user->save();

        $clientId = Client::bringNameByToken($request->header('Authorization'))->id;

        $receipt = Receipt::store($id, $clientId, null, 0, 0, $oldPoints, $user->points, 0);

        Queue::pushOn('xsolla-recharge', new XsollaRechargeJob($user, $request->input('point'), '이용자 포인트 지급'));

        app(DiscordNotificationController::class)->point($user->email, $user->discord_id, $request->input('point'), $user->points);

        return new JsonResponse(['receipt_id' => $receipt->id]);
    }
}
