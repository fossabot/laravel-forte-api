<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\XsollaUrl.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\XsollaUrl query()
 * @mixin \Eloquent
 */
class XsollaUrl extends Model
{
    const TOKEN = 'token';
    const REDIRECT_URL = 'redirect_url';
    const HIT = 'hit';
    const USER_ID = 'user_id';
    const EXPIRED = 'expired';

    protected $fillable = [
        self::TOKEN, self::REDIRECT_URL, self::HIT, self::USER_ID, self::EXPIRED
    ];
}
