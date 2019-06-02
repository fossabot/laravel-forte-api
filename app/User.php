<?php

namespace App;

use Illuminate\Support\Facades\DB;
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
     * @brief 1:1 relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function discord()
    {
        return $this->hasOne(Discord::class, 'id', 'user_id');
    }

    /**
     * @return mixed
     */
    public static function scopeAllUsers()
    {
        return self::whereNull('withdraw_at')->get();
    }

    /**
     * see a user who been withdraw.
     *
     * @param int $id
     * @return mixed
     */
    public static function scopeGetUser(int $id)
    {
        try {
            return self::findOrFail($id);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @param string $id
     * @return User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function scopeGetUserById(string $id)
    {
        return self::where('name', $id)->orWhere('email', $id)->first();
    }

    /**
     * @param int $id
     * @param array $datas
     * @return array
     * @throws \Exception
     */
    public static function scopeUpdateUser(int $id, array $datas = [])
    {
        $xsollaAPI = \App::make('App\Services\XsollaAPIService');
        $user = self::find($id);
        try {
            DB::beginTransaction();

            foreach ($datas as $key => $data) {
                if (self::where($key, $data)->first()) {
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

            $xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/'.$id, $datas);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            (new \App\Http\Controllers\DiscordNotificationController)->exception($exception, $datas);

            return ['error' => $exception->getMessage()];
        }

        return $user;
    }

    /**
     * @param int $id
     * @return array
     */
    public static function scopeDestoryUser(int $id)
    {
        $xsollaAPI = \App::make('App\Services\XsollaAPIService');

        self::where('id', $id)->update([
            'withdraw_at' => date('Y-m-d'),
        ]);

        $datas = [
            'enabled' => false,
        ];

        $xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/'.$id, $datas);

        return ['message' => 'success'];
    }
}
