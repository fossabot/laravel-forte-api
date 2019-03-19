<?php

namespace App;

use Illuminate\Support\Facades\DB;
use App\Services\XsollaAPIService;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'points',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @return mixed
     */
    static public function scopeAllUsers() {
        return self::whereNull('withdraw_at')->get();
    }

    /**
     * see a user who been withdraw
     *
     * @param int $id
     * @return mixed
     */
    static public function scopeGetUser(int $id) {
        return self::where('id', $id)->first();
    }

    /**
     * @param string $id
     * @return User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    static public function scopeGetUserById(string $id) {
        return self::where('name', $id)->orWhere('email', $id)->first();
    }

    /**
     * @param int $id
     * @param array $datas
     * @return array
     * @throws \Exception
     */
    static public function scopeUpdateUser(int $id, array $datas = []) {
        $xsollaAPI = \App::make('App\Services\XsollaAPIService');
        $user = self::find($id);
        try {
            DB::beginTransaction();

            foreach ($datas as $key => $data) {
                if (User::where($key, $data)->first()) {
                    continue;
                }
                $user->$key = $data;
            }
            $user->save();

            $datas = [
                'enabled' => true,
                'user_name' => $user->name,
                'email' => $user->email,
            ];

            $xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/' . $id, $datas);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            (new \App\Http\Controllers\DiscordNotificationController)->exception($e, $datas);
            return ['error' => $e->getMessage()];
        }

        return $user;
    }

    /**
     * @param int $id
     * @return array
     */
    static public function scopeDestoryUser(int $id) {
        self::where('id', $id)->update([
            'withdraw_at' => date('Y-m-d')
        ]);

        return ['message' => 'success'];
    }
}
