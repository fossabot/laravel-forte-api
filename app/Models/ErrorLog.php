<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ErrorLog
 *
 * @property int $id
 * @property string $environment 환경
 * @property string $title
 * @property string $message
 * @property string $parameters
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ErrorLog whereUpdatedAt($value)
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
