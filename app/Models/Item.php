<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Item.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserItem[] $userItems
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item allItemLists()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item itemDetail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Item skuParseId()
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
