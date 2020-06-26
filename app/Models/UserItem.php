<?php

namespace App\Models;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as BuilderAlias;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserItem.
 *
 * @property int $id
 * @property int $user_id
 * @property int $item_id
 * @property int $expired whether expiration time has passed or the cash was refunded
 * @property int $consumed
 * @property int $sync whether bot(items.client_id) is notified of the change in this item
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Item $item
 * @method static Builder|UserItem countUserPurchaseDuplicateItem($itemId)
 * @method static Builder|UserItem destroyUserItem($itemId)
 * @method static bool|null forceDelete()
 * @method static Builder|UserItem newModelQuery()
 * @method static Builder|UserItem newQuery()
 * @method static BuilderAlias|UserItem onlyTrashed()
 * @method static Builder|UserItem purchaseUserItem($itemId, $token)
 * @method static Builder|UserItem query()
 * @method static bool|null restore()
 * @method static Builder|UserItem updateUserItem($itemId, $data, $token)
 * @method static Builder|UserItem userItemDetail($itemId)
 * @method static Builder|UserItem userItemLists()
 * @method static Builder|UserItem userItemWithdraw()
 * @method static Builder|UserItem whereConsumed($value)
 * @method static Builder|UserItem whereCreatedAt($value)
 * @method static Builder|UserItem whereDeletedAt($value)
 * @method static Builder|UserItem whereExpired($value)
 * @method static Builder|UserItem whereId($value)
 * @method static Builder|UserItem whereItemId($value)
 * @method static Builder|UserItem whereSync($value)
 * @method static Builder|UserItem whereUpdatedAt($value)
 * @method static Builder|UserItem whereUserId($value)
 * @method static BuilderAlias|UserItem withTrashed()
 * @method static BuilderAlias|UserItem withoutTrashed()
 * @mixin Eloquent
 * @property-read User $user
 * @method static Builder|UserItem item($id)
 * @method static Builder|UserItem ofUser($id)
 * @method static Builder|UserItem ofItem($id)
 * @property-read \App\Models\Item|null $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserItem ofId($id)
 */
class UserItem extends Model
{
    use SoftDeletes;

    const SBK_5 = 'skb_5';
    const SKB_9 = 'skb_9';
    const SKB_12 = 'skb_12';

    const DISABLE_WITHDRAW_ITEMS = [
        self::SBK_5,
        self::SKB_9,
        self::SKB_12,
    ];

    const ID = 'id';
    const USER_ID = 'user_id';
    const ITEM_ID = 'item_id';
    const EXPIRED = 'expired';
    const CONSUMED = 'consumed';
    const SYNC = 'sync';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::USER_ID, self::ITEM_ID, self::EXPIRED, self::CONSUMED, self::SYNC,
    ];

    protected $dates = [
        self::DELETED_AT,
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasOne
     */
    public function items(): HasOne
    {
        return $this->hasOne(Item::class, Item::ID, self::ITEM_ID);
    }

    /**
     * @param Builder $query
     * @param int $id
     * @return Builder
     */
    public function scopeOfItem(Builder $query, int $id): Builder
    {
        return $query->where(self::ITEM_ID, $id);
    }

    /**
     * @param Builder $query
     * @param int $id
     * @return Builder
     */
    public function scopeOfId(Builder $query, int $id): Builder
    {
        return $query->where(self::ID, $id);
    }

    /**
     * @param Builder $query
     * @param int $id
     * @return Builder
     */
    public function scopeOfUser(Builder $query, int $id): Builder
    {
        return $query->where(self::USER_ID, $id);
    }
}
