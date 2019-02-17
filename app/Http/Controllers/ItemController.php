<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Item;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
    public function index() {
        return response()->json(Item::scopeAllItemLists());
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
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
    public function show(int $id) {
        return response()->json(Item::scopeItemDetail($id));
    }
}
