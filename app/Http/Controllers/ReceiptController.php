<?php

namespace App\Http\Controllers;

use App\Models\Receipt;

class ReceiptController extends Controller
{
    /**
     * 이용자의 모든 레시피 정보를 조회합니다.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/users/{userId}/receipts",
     *     description="Show the Receipts have user",
     *     produces={"application/json"},
     *     tags={"Receipt"},
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
     *         description="Successful User Receipts Information"
     *     ),
     * )
     */
    public function index(int $id)
    {
        return response()->json(Receipt::scopeUserReceiptLists($id));
    }

    /**
     * 이용자의 상세 레시피 정보를 조회합니다.
     *
     * @param int $id
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/users/{userId}/receipts/{receiptId}",
     *     description="Show the Receipt have user",
     *     produces={"application/json"},
     *     tags={"Receipt"},
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
     *         name="receiptId",
     *         in="path",
     *         description="User Receipt Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Receipt Information"
     *     ),
     * )
     */
    public function show(int $id, int $receiptId)
    {
        return response()->json(Receipt::scopeUserReceiptDetail($id, $receiptId));
    }
}
