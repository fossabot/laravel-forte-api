<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserItem;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserItemController extends Controller
{
    /**
     * 이용자의 보유한 아이템 목록을 조회합니다.
     *
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *     path="/users/{userId}/items",
     *     description="Show the items have user",
     *     produces={"application/json"},
     *     tags={"User Item"},
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
     *         description="Successful User Items Information"
     *     ),
     * )
     */
    public function index(int $id): JsonResponse
    {
        return new JsonResponse(UserItem::scopeUserItemLists($id));
    }

    /**
     * 아이템을 구매합니다.
     *
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws Exception
     *
     * @SWG\Post(
     *     path="/users/{userId}/items",
     *     description="Store(save) the User buying Item",
     *     produces={"application/json"},
     *     tags={"User Item"},
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
     *          name="item_id",
     *          in="query",
     *          description="Item Id",
     *          required=true,
     *          type="string"
     *      ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Buying Item"
     *     ),
     * )
     */
    public function store(Request $request, int $id): JsonResponse
    {
        if (isset($request->item_id) && User::scopeGetUser($id)) {
            return new JsonResponse(UserItem::scopePurchaseUserItem($id, $request->item_id, $request->header('Authorization')));
        }

        return new JsonResponse([
            'message' => 'Not found Item Id or User Id',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * 이용자가 보유중인 아이템 상세 정보를 조회합니다.
     *
     * @param int $id
     * @param int $itemId
     * @return JsonResponse
     *
     * @SWG\Get(
     *     path="/users/{userId}/items/{userItemId}",
     *     description="Show the item have user",
     *     produces={"application/json"},
     *     tags={"User Item"},
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
     *         name="userItemId",
     *         in="path",
     *         description="User Item Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Item Information"
     *     ),
     * )
     */
    public function show(int $id, int $itemId): JsonResponse
    {
        return new JsonResponse(UserItem::scopeUserItemDetail($id, $itemId));
    }

    /**
     * 이용자의 아이템 정보를 갱신합니다.
     *
     * @param Request $request
     * @param int $id
     * @param int $itemId
     * @return JsonResponse
     * @throws Exception
     * @SWG\Put(
     *     path="/users/{userId}/items/{userItemId}",
     *     description="Update User Item",
     *     produces={"application/json"},
     *     tags={"User Item"},
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
     *         name="userItemId",
     *         in="path",
     *         description="User Item Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="sync",
     *         in="query",
     *         description="sync",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="expired",
     *         in="query",
     *         description="expired",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="consumed",
     *         in="query",
     *         description="consumed",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Item Update"
     *     ),
     * )
     */
    public function update(Request $request, int $id, int $itemId): JsonResponse
    {
        return new JsonResponse(UserItem::scopeUpdateUserItem($id, $itemId, $request->all(), $request->header('Authorization')));
    }

    /**
     * 이용자의 아이템을 제거합니다.
     *
     * @param int $id
     * @param int $itemId
     * @return JsonResponse
     *
     * @SWG\Delete(
     *     path="/users/{userId}/items/{userItemId}",
     *     description="Destroy User Item",
     *     produces={"application/json"},
     *     tags={"User Item"},
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
     *         name="userItemId",
     *         in="path",
     *         description="User Item Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful Destroy User Item"
     *     ),
     * )
     */
    public function destroy(int $id, int $itemId): JsonResponse
    {
        return new JsonResponse(UserItem::scopeDestroyUserItem($id, $itemId));
    }

    /**
     * 이용자 아이템 청약철회.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function withdraw(Request $request): JsonResponse
    {
        $item = UserItem::scopeUserItemDetail(Auth::User()->id, $request->id);

        if (in_array($item->sku, UserItem::DISABLE_WITHDRAW_ITEMS)) {
            return new JsonResponse(['message' => 'Upon purchase is considered to be used this item for withdrawal is not possible.'], 403);
        }

        if ($item) {
            return new JsonResponse(UserItem::scopeUserItemWithdraw($request->id));
        }

        return new JsonResponse(['message' => 'ERROR'], Response::HTTP_BAD_REQUEST);
    }
}
