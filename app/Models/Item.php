<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserItem[] $userItems
 * @property-read int|null $user_items_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item allItemLists()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item itemDetail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item skuParseId()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereConsumable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereExpirationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item wherePurchaseLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Item extends Model
{
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
     * @brief 1:n relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userItems()
    {
        return $this->hasMany(UserItem::class);
    }

    /**
     * @return mixed
     */
    public static function scopeAllItemLists()
    {
        return self::get();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function scopeItemDetail(int $id)
    {
        return self::find($id)->first();
    }

    /**
     * @param string $sku
     * @return mixed
     */
    public static function scopeSkuParseId(string $sku)
    {
        return self::where(self::SKU, $sku)->first()->id;
    }
}
