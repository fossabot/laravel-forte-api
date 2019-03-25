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
     * @return mixed
     */
    static public function scopeAllItemLists() {
        return self::get();
    }

    /**
     * @param int $id
     * @return mixed
     */
    static public function scopeItemDetail(int $id) {
        return self::where('id', $id)->first();
    }

    /**
     * @param string $sku
     * @return mixed
     */
    static public function scopeSkuParseId(string $sku) {
        return self::where('sku', $sku)->first()->id;
    }

}
