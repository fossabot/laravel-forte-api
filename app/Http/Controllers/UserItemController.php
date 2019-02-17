<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\User;
use App\UserItem;

class UserItemController extends Controller {
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
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
    public function index(int $id) {
        return response()->json(UserItem::scopeUserItemLists($id));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $id
     * @return mixed
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
    public function store(Request $request, int $id) {
        if (isset($request->item_id) && User::scopeGetUser($id)) {
            return response()->json(UserItem::scopePurchaseUserItem($id, $request->item_id, $request->header('Authorization')));
        } else {
            return response()->json([
                'message' => 'Not found Item Id or User Id',
            ], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param int $itemId
     * @return mixed
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
    public function show(int $id, int $itemId) {
        return response()->json(UserItem::scopeUserItemDetail($id, $itemId));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @param int $itemId
     * @return \Illuminate\Http\Response
     *
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
     *         required=false,
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
    public function update(Request $request, int $id, int $itemId) {
        return response()->json(UserItem::scopeUpdateUserItem($id, $itemId, $request->all(), $request->header('Authorization')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param int $itemId
     * @return mixed
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
    public function destroy(int $id, int $itemId) {
        return response()->json(UserItem::scopeDestroyUserItem($id, $itemId));
    }
}
