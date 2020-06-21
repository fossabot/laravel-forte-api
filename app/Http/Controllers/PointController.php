<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Receipt;
use App\Models\User;
use App\Services\XsollaAPIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $users = User::scopeAllStaffs();

        foreach ($users as $user) {
            $oldPoints = $user->{User::POINTS};
            $user->{User::POINTS} += self::MAX_POINT;
            $user->save();

            $receipt = Receipt::store($user->{User::ID}, 5, null, 0, 0, $oldPoints, $user->{User::POINTS}, 0);

            $this->recharge(self::MAX_POINT, '스태프 포인트 지급', $receipt->{Receipt::USER_ID});

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
        $user = User::scopeGetUser($id);

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

        $this->recharge($request->points, '이용자 포인트 지급', $receipt->{Receipt::USER_ID});

        (new DiscordNotificationController)->point($user->{User::EMAIL}, $user->{User::DISCORD_ID}, $request->{User::POINTS}, $user->{User::POINTS});

        return new JsonResponse(['receipt_id' => $receipt->{Receipt::ID}]);
    }

    /**
     * @param int $point
     * @param string $comment
     * @param int $userId
     */
    public function recharge(int $point, string $comment, int $userId): void
    {
        $user = User::find($userId);
        $needPoint = 0;
        $repetition = false;

        while (true) {
            $datas = [
                'amount' => $repetition ? $needPoint : $point,
                'comment' => $comment,
                'project_id' => config('xsolla.projectKey'),
                'user_id' => $userId,
            ];

            $response = json_decode($this->xsollaAPI->requestAPI('POST', 'projects/:projectId/users/'.$userId.'/recharge', $datas), true);

            if ($user->{User::POINTS} !== $response['amount']) {
                $repetition = true;
                $needPoint = $user->{User::POINTS} - $response['amount'];
                continue;
            } else {
                break;
            }
        }
    }
}
