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
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'sku', 'name', 'image_url', 'price', 'enabled', 'consumable', 'expiration_time', 'purchase_limit',
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
        return self::where('id', $id)->first();
    }

    /**
     * @param string $sku
     * @return mixed
     */
    public static function scopeSkuParseId(string $sku)
    {
        return self::where('sku', $sku)->first()->id;
    }
}
