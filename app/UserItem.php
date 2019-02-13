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
}
