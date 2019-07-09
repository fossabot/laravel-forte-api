<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XsollaUrl extends Model
{
    protected $fillable = [
        'token', 'redirect_url', 'hit',
    ];
}
