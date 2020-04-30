<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ErrorLog.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog query()
 * @mixin \Eloquent
 */
class ErrorLog extends Model
{
    const ENVIRONMENT = 'environment';
    const TITLE = 'title';
    const MESSAGE = 'message';
    const PARAMETERS = 'parameters';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        self::ENVIRONMENT, self::TITLE, self::MESSAGE, self::PARAMETERS
    ];
}
