<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterFormRequest;

use App\User;

class UserController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @SWG\Get(
     *     path="/users",
     *     description="List Users",
     *     produces={"application/json"},
     *     tags={"User"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="User Lists"
     *     ),
     * )
     */
    public function index() {
        return response()->json(User::scopeAllUsers());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RegisterFormRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     *
     * @SWG\Post(
     *     path="/users",
     *     description="Store(save) the User Information",
     *     produces={"application/json"},
     *     tags={"User"},
     *      @SWG\Parameter(
     *          name="Authorization",
     *          in="header",
     *          description="Authorization Token",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="name",
     *          in="query",
     *          description="User Name",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="email",
     *          in="query",
     *          description="User Email",
     *          required=true,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          in="query",
     *          description="User Password",
     *          required=true,
     *          type="string"
     *      ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Create User Information"
     *     ),
     * )
     */
    public function store(RegisterFormRequest $request) {
        $user = new User;
        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        $user->save();

        if ($user) {
            return response()->json([
                'status' => 'success',
                'data' => $user
            ], 201);
        } else {
            return response()->json([
                'error' => 'test'
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     *
     * @SWG\Get(
     *     path="/users/{userId}",
     *     description="Show User Information",
     *     produces={"application/json"},
     *     tags={"User"},
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
     *         description="Successful User Information"
     *     ),
     * )
     */
    public function show(int $id) {
        return response()->json(User::scopeGetUser($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     *
     * @SWG\Put(
     *     path="/users/{userId}",
     *     description="Update User Information",
     *     produces={"application/json"},
     *     tags={"User"},
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
     *         name="name",
     *         in="query",
     *         description="User Name",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="query",
     *         description="User Email",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="query",
     *         description="User Password",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Information Update"
     *     ),
     * )
     */
    public function update(Request $request, int $id) {
        return response()->json(User::scopeUpdateUser($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     *
     * @SWG\Delete(
     *     path="/users/{userId}",
     *     description="Destroy User",
     *     produces={"application/json"},
     *     tags={"User"},
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
     *         description="Successful Destroy User"
     *     ),
     * )
     */
    public function destroy(int $id) {
        return response()->json(User::scopeDestoryUser($id));
    }
}
