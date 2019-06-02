<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
