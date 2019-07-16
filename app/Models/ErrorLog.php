<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ErrorLog
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog query()
 * @mixin \Eloquent
 */
class ErrorLog extends Model
{
    protected $fillable = [
        'environment', 'title', 'message', 'parameters',
    ];
}
