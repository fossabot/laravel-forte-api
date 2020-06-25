<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Item
 *
 * @property int $id
 * @property int $client_id discord bot related to the item
 * @property string $sku unique item code from xsolla
 * @property string $name
 * @property string $image_url
 * @property int $price item price in points
 * @property int $enabled whether the item is on sale
 * @property int $consumable
 * @property int|null $expiration_time expiration time in seconds (NULL means permanent)
 * @property int|null $purchase_limit max purchase count per user (NULL means infinity)
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|UserItem[] $userItems
 * @property-read int|null $user_items_count
 * @method static Builder|Item allItemLists()
 * @method static Builder|Item itemDetail()
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item query()
 * @method static Builder|Item skuParseId()
 * @method static Builder|Item whereClientId($value)
 * @method static Builder|Item whereConsumable($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereEnabled($value)
 * @method static Builder|Item whereExpirationTime($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereImageUrl($value)
 * @method static Builder|Item whereName($value)
 * @method static Builder|Item wherePrice($value)
 * @method static Builder|Item wherePurchaseLimit($value)
 * @method static Builder|Item whereSku($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|UserItem[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item ofSku($sku)
 */
class Item extends Model
{
    const ID = 'id';
    const CLIENT_ID = 'client_id';
    const SKU = 'sku';
    const NAME = 'name';
    const IMAGE_URL = 'image_url';
    const PRICE = 'price';
    const ENABLED = 'enabled';
    const CONSUMABLE = 'consumable';
    const EXPIRATION_TIME = 'expiration_time';
    const PURCHASE_LIMIT = 'purchase_limit';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::CLIENT_ID,
        self::SKU,
        self::NAME,
        self::IMAGE_URL,
        self::PRICE,
        self::ENABLED,
        self::CONSUMABLE,
        self::EXPIRATION_TIME,
        self::PURCHASE_LIMIT,
    ];

    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * @return Collection
     */
    public static function scopeAllItemLists(): Collection
    {
        return self::get();
    }

    /**
     * @param int $id
     * @return Item
     */
    public static function scopeItemDetail(int $id): self
    {
        return self::find($id);
    }

    /**
     * @param string $sku
     * @return int
     */
    public static function convertSkuToId(string $sku): int
    {
        return self::where(self::SKU, $sku)->first()->{self::ID};
    }

    /**
     * @param Builder $query
     * @param string $sku
     * @return Builder
     */
    public function scopeOfSku(Builder $query, string $sku): Builder
    {
        return $query->where(self::SKU, $sku);
    }
}
