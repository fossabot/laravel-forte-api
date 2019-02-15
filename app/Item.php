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
}
