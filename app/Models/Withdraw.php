<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    const ID = 'id';
    const USER_ID = 'user_id';
    const ITEM_ID = 'item_id';
    const USER_ITEM_ID = 'user_item_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        self::USER_ID, self::ITEM_ID, self::USER_ITEM_ID,
    ];
}
