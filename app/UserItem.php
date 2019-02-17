<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'item_id', 'expired', 'consumed', 'sync',
    ];

    /**
     * @param int $id
     * @return mixed
     */
    static public function scopeUserItemLists(int $id) {
        return self::join('user_items', 'items.id', '=', 'user_items.item_id')->where('user_items.user_id', $id)->get();
    }

    /**
     * @param int $id
     * @param int $itemId
     * @return mixed
     */
    static public function scopeUserItemDetail(int $id, int $itemId) {
        return self::join('user_items', 'items.id', '=', 'user_items.item_id')->where('user_items.user_id', $id)->
        where('user_items.item_id', $itemId)->first();
    }
}
