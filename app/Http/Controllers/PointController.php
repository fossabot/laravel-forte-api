<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\User;
use App\Receipt;
use App\Client;

class PointController extends Controller
{
    /**
     * Store a newly created resource in storage.
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
    public function store(Request $request, int $id) {
        $user = User::scopeGetUser($id);
        if (! empty($user->withdraw_at)) {
            return response([
                'message' => 'Withdraw User Account',
            ], 400);
        }

        if (! $user) {
            return response([
                'message' => 'User does not exist',
            ], 404);
        }

        $oldPoints = $user->points;
        $user->points += $request->points;
        $user->save();

        $receipt = new Receipt;
        $receipt->user_id = $id;
        $receipt->client_id = Client::bringNameByToken($request->header('Authorization'))->id;
        $receipt->user_item_id = NULL;
        $receipt->about_cash = 0;
        $receipt->refund = 0;
        $receipt->points_old = $oldPoints;
        $receipt->points_new = $user->points;
        $receipt->save();

        return ['receipt_id' => $receipt->id];
    }
}
