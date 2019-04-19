<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Hash;

class UserService {
    /**
     * @param int $id
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     */
    public function authentication(int $id, string $password) {
        $user = User::where('id', $id)->first();

        if (Hash::check($password, $user->password)) {
            return response()->json([
                'message' => '204 No Content'
            ], 204);
        } else {
            return response()->json([
                'message' => '403 Forbidden'
            ], 403);
        }
    }
}
