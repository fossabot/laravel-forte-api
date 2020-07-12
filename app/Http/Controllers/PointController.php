<?php

namespace App\Http\Controllers;

use App\Jobs\XsollaRechargeJob;
use App\Models\Client;
use App\Models\Receipt;
use App\Models\User;
use App\Services\XsollaAPIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Queue;

class PointController extends Controller
{
    private const STAFF_TYPE = 2;
    private const MAX_POINT = 2000;

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
        $users = User::ofType(self::STAFF_TYPE);

        foreach ($users as $user) {
            $oldPoints = $user->points;
            $user->{User::POINTS} += self::MAX_POINT;
            $user->save();

            $receipt = Receipt::store($user->{User::ID}, 5, null, 0, 0, $oldPoints, $user->{User::POINTS}, 0);

            Queue::pushOn('xsolla recharge', new XsollaRechargeJob($user, self::MAX_POINT, '스태프 포인트 지급'));

            (new DiscordNotificationController)->point($user->{User::EMAIL}, $user->{User::DISCORD_ID}, self::MAX_POINT, $user->{User::POINTS});
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
    public function store(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return new JsonResponse([
                'message' => 'User does not exist',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($user->trashed()) {
            return new JsonResponse([
                'message' => 'Withdraw User Account',
            ], Response::HTTP_BAD_REQUEST);
        }

        $oldPoints = $user->{User::POINTS};
        $user->{User::POINTS} += $request->points;
        $user->save();

        $clientId = Client::bringNameByToken($request->header('Authorization'))->id;

        $receipt = Receipt::store($id, $clientId, null, 0, 0, $oldPoints, $user->{User::POINTS}, 0);

        Queue::pushOn('xsolla recharge', new XsollaRechargeJob($user, $request->points, '이용자 포인트 지급'));

        (new DiscordNotificationController)->point($user->{User::EMAIL}, $user->{User::DISCORD_ID}, $request->{User::POINTS}, $user->{User::POINTS});

        return new JsonResponse(['receipt_id' => $receipt->{Receipt::ID}]);
    }
}
