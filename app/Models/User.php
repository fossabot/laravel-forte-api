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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property-read Attendance $attendance
 * @property-read Collection|UserItem[] $items
 * @property-read int|null $items_count
 * @property-read Collection|Receipt[] $receipts
 * @property-read int|null $receipts_count
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    const ID = 'id';
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
        self::NAME, self::DISCORD_ID, self::EMAIL, self::POINTS, self::DELETED_AT,
    ];

    protected $dates = [self::DELETED_AT];

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * @return HasMany
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * @return HasOne
     */
    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class, self::DISCORD_ID);
    }

    /**
     * @param Builder $query
     * @param int $type
     * @return Builder
     */
    public function scopeOfType(Builder $query, int $type): Builder
    {
        return $query->where(self::IS_MEMBER, $type);
    }
}
