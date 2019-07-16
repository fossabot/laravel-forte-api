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
    protected $fillable = [
        'token', 'redirect_url', 'hit',
    ];
}
