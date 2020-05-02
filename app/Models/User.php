<?php

namespace App\Models;

use App;
use App\Http\Controllers\DiscordNotificationController;
use Eloquent;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $discord_id
 * @property string $name username
 * @property string|null $email
 * @property int $points virtual currency balance
 * @property int $is_member 0: default, 1: support, 2: staff
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|User allStaffs()
 * @method static Builder|User allUsers()
 * @method static Builder|User createUser()
 * @method static Builder|User destoryUser()
 * @method static bool|null forceDelete()
 * @method static Builder|User getUser()
 * @method static Builder|User getUserByDiscordId()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User query()
 * @method static bool|null restore()
 * @method static Builder|User updateUser($datas = [])
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereDiscordId($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIsMember($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePoints($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin Eloquent
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
     * @param $user
     * @return User|Model
     */
    public static function scopeCreateUser($user): User
    {
        return self::create([
            self::EMAIL => $user->{self::EMAIL},
            self::NAME => $user->{self::NAME},
            self::DISCORD_ID => $user->id,
        ]);
    }

    /**
     * @return mixed
     */
    public static function scopeAllUsers(): Paginator
    {
        return self::whereNull(self::DELETED_AT)->paginate();
    }

    /**
     * see a user who been withdraw.
     *
     * @param int $id
     * @return mixed
     */
    public static function scopeGetUser(int $id): User
    {
        try {
            return self::findOrFail($id);
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @param string $id
     * @return User|Builder|Model|object|null
     */
    public static function scopeGetUserByDiscordId(string $id): User
    {
        return self::where(self::DISCORD_ID, $id)->first();
    }

    /**
     * @param int $id
     * @param array $datas
     * @return array
     * @throws Exception
     */
    public static function scopeUpdateUser(int $id, array $datas = [])
    {
        $xsollaAPI = App::make('App\Services\XsollaAPIService');
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
                'user_name' => $user->{self::NAME},
                'email' => $user->{self::EMAIL},
            ];

            $xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/'.$id, $datas);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            (new DiscordNotificationController)->exception($exception, $datas);

            return ['error' => $exception->getMessage()];
        }

        return $user;
    }

    /**
     * @param int $id
     * @return User|\Illuminate\Database\Query\Builder
     */
    public static function scopeDestoryUser(int $id): User
    {
        $xsollaAPI = App::make('App\Services\XsollaAPIService');

        $datas = [
            'enabled' => false,
        ];

        $xsollaAPI->requestAPI('PUT', 'projects/:projectId/users/'.$id, $datas);

        return self::withTrashed()->find($id);
    }

    /**
     * @return Collection
     */
    public static function scopeAllStaffs(): Collection
    {
        return self::where(self::IS_MEMBER, '=', 2)->whereNull('deleted_at')->get();
    }
}
