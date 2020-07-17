<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemController extends Controller
{
    /**
     * 전체 아이템을 조회합니다.
     *
     * @return JsonResponse
     *
     * @SWG\Get(
     *     path="/items",
     *     description="List Items",
     *     produces={"application/json"},
     *     tags={"Item"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful Item Lists"
     *     ),
     * )
     */
    public function index(): JsonResponse
    {
        $items = Item::get();

        return new JsonResponse($items);
    }

    /**
     * 아이템을 상세 조회합니다.
     *
     * @param int $id
     * @return JsonResponse
     * @throws NotFoundHttpException
     *
     * @SWG\Get(
     *     path="/items/{itemId}",
     *     description="Show Item Information",
     *     produces={"application/json"},
     *     tags={"Item"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="itemId",
     *         in="path",
     *         description="Item Id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful Item Information"
     *     ),
     * )
     */
    public function show(int $id): JsonResponse
    {
        $item = Item::find($id);

        if (is_null($item)) {
            throw new NotFoundHttpException('해당 아이템이 존재하지 않습니다.');
        }

        return new JsonResponse($item);
    }
}
