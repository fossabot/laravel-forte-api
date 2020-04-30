<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\User.
 *
 * @property-read \App\Models\Discord $discord
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User allUsers()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User destoryUser()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User getUser()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User getUserById()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User updateUser($datas = array())
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    const NAME = 'name';
    const EMAIL = 'email';
    const POINTS = 'points';
    const DISCORD_ID = 'discord_id';
    const IS_MEMBER = 'is_member';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::NAME, self::DISCORD_ID, self::EMAIL, self::POINTS,
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
    public static function scopeAllUsers()
    {
        return self::whereNull(self::DELETED_AT)->get();
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
    public static function scopeGetUserByDiscordId(string $id)
    {
        return self::where(self::DISCORD_ID, $id)->first();
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

        self::find($id)->update([
            self::DELETED_AT => date('Y-m-d'),
        ]);

        $datas = [
            'enabled' => false,
        ];

        $xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/'.$id, $datas);

        return ['message' => 'success'];
    }

    /**
     * @return mixed
     */
    public static function scopeAllStaffs()
    {
        self::where(self::IS_MEMBER, '=', 2)->whereNull('deleted_at')->get();
    }
}
